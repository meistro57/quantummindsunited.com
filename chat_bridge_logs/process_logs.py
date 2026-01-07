import re
import mysql.connector
import zipfile
import shutil
from datetime import datetime
from pathlib import Path
from typing import Dict, List, Optional

class ChatBridgeProcessor:
    def __init__(self, db_config: Dict[str, str], archive_folder: str = 'archive'):
        """
        Initialize processor with database configuration.

        db_config example:
        {
            'host': 'localhost',
            'user': 'your_user',
            'password': 'your_password',
            'database': 'chat_bridge'
        }
        archive_folder: Folder where processed logs will be archived (default: 'archive')
        """
        self.db_config = db_config
        self.conn = None
        self.cursor = None
        self.archive_folder = Path(archive_folder)
    
    def connect(self):
        """Establish database connection."""
        self.conn = mysql.connector.connect(**self.db_config)
        self.cursor = self.conn.cursor()
    
    def disconnect(self):
        """Close database connection."""
        if self.cursor:
            self.cursor.close()
        if self.conn:
            self.conn.close()

    def archive_log(self, filepath: str) -> bool:
        """
        Archive a processed log file by zipping it into the archive folder.

        Args:
            filepath: Path to the log file to archive

        Returns:
            True if successful, False otherwise
        """
        try:
            filepath = Path(filepath)

            # Create archive folder if it doesn't exist
            self.archive_folder.mkdir(exist_ok=True)

            # Create zip filename with timestamp
            timestamp = datetime.now().strftime('%Y%m%d')
            zip_filename = f"{filepath.stem}_{timestamp}.zip"
            zip_path = self.archive_folder / zip_filename

            # Create zip archive
            with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
                zipf.write(filepath, filepath.name)

            # Delete original file after successful archiving
            filepath.unlink()

            print(f"Archived: {filepath.name} -> {zip_path}")
            return True

        except Exception as e:
            print(f"Error archiving file {filepath}: {e}")
            return False
    
    def parse_markdown_file(self, filepath: str) -> Dict:
        """Parse markdown log file and extract structured data."""
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        data = {
            'session': {},
            'agents': [],
            'messages': []
        }
        
        # Extract session ID and started time
        session_id_match = re.search(r'\*\*Session ID:\*\* (conv_\d+_\d+)', content)
        started_match = re.search(r'\*\*Started:\*\* ([\d-]+ [\d:]+)', content)
        starter_match = re.search(r'\*\*Conversation Starter:\*\* (.+?)(?=\n\n|\n###)', content, re.DOTALL)
        
        data['session']['session_id'] = session_id_match.group(1) if session_id_match else None
        data['session']['started_at'] = started_match.group(1) if started_match else None
        data['session']['conversation_starter'] = starter_match.group(1).strip() if starter_match else None
        
        # Extract session settings
        max_rounds_match = re.search(r'\*\*Max Rounds:\*\* (\d+)', content)
        memory_rounds_match = re.search(r'\*\*Memory Rounds:\*\* (\d+)', content)
        stop_word_match = re.search(r'\*\*Stop Word Detection:\*\* (\w+)', content)
        stop_words_match = re.search(r'\*\*Stop Words:\*\* (.+)', content)
        
        data['session']['max_rounds'] = int(max_rounds_match.group(1)) if max_rounds_match else None
        data['session']['memory_rounds'] = int(memory_rounds_match.group(1)) if memory_rounds_match else None
        data['session']['stop_word_detection'] = stop_word_match.group(1) == 'Enabled' if stop_word_match else False
        data['session']['stop_words'] = stop_words_match.group(1) if stop_words_match else None
        
        # Extract Agent A configuration
        agent_a = {
            'label': 'A',
            'provider': self._extract_field(content, 'Agent A Provider'),
            'model': self._extract_field(content, 'Agent A Model'),
            'temperature': self._extract_float(content, 'Agent A Temperature'),
            'persona': self._extract_field(content, 'Agent A Persona'),
            'system_prompt': self._extract_multiline_field(content, 'Agent A System Prompt')
        }
        data['agents'].append(agent_a)
        
        # Extract Agent B configuration
        agent_b = {
            'label': 'B',
            'provider': self._extract_field(content, 'Agent B Provider'),
            'model': self._extract_field(content, 'Agent B Model'),
            'temperature': self._extract_float(content, 'Agent B Temperature'),
            'persona': self._extract_field(content, 'Agent B Persona'),
            'system_prompt': self._extract_multiline_field(content, 'Agent B System Prompt')
        }
        data['agents'].append(agent_b)
        
        # Extract conversation messages
        conversation_section = re.search(r'## Conversation\n\n(.+)', content, re.DOTALL)
        if conversation_section:
            messages_text = conversation_section.group(1)
            # Updated pattern to handle optional <sub> tag for provider/model info
            message_pattern = r'### (Human|Agent A|Agent B) \(([\d-]+ [\d:]+)\)\n(?:<sub[^>]*>.*?</sub>\n)?\n?(.+?)(?=\n### |\Z)'

            for i, match in enumerate(re.finditer(message_pattern, messages_text, re.DOTALL)):
                speaker = match.group(1)
                timestamp = match.group(2)
                message_content = match.group(3).strip()

                # Skip the <sub> tag content from the message body if present
                message_content = re.sub(r'^<sub[^>]*>.*?</sub>\s*', '', message_content, flags=re.DOTALL)

                data['messages'].append({
                    'speaker': speaker,
                    'timestamp': timestamp,
                    'content': message_content,
                    'order': i
                })
        
        return data
    
    def _extract_field(self, content: str, field_name: str) -> Optional[str]:
        """Extract single-line field value."""
        match = re.search(rf'\*\*{field_name}:\*\* (.+)', content)
        return match.group(1).strip() if match else None
    
    def _extract_float(self, content: str, field_name: str) -> Optional[float]:
        """Extract float field value."""
        match = re.search(rf'\*\*{field_name}:\*\* ([\d.]+)', content)
        return float(match.group(1)) if match else None
    
    def _extract_multiline_field(self, content: str, field_name: str) -> Optional[str]:
        """Extract multi-line field value."""
        match = re.search(rf'\*\*{field_name}:\*\* (.+?)(?=\n\n\*\*|\n###)', content, re.DOTALL)
        return match.group(1).strip() if match else None
    
    def insert_session(self, session_data: Dict) -> bool:
        """Insert session data into database."""
        query = """
        INSERT INTO sessions 
        (session_id, started_at, conversation_starter, max_rounds, 
         memory_rounds, stop_word_detection, stop_words)
        VALUES (%s, %s, %s, %s, %s, %s, %s)
        ON DUPLICATE KEY UPDATE
        started_at = VALUES(started_at),
        conversation_starter = VALUES(conversation_starter)
        """
        
        try:
            self.cursor.execute(query, (
                session_data['session_id'],
                session_data['started_at'],
                session_data['conversation_starter'],
                session_data['max_rounds'],
                session_data['memory_rounds'],
                session_data['stop_word_detection'],
                session_data['stop_words']
            ))
            return True
        except mysql.connector.Error as e:
            print(f"Error inserting session: {e}")
            return False
    
    def insert_agents(self, session_id: str, agents: List[Dict]) -> bool:
        """Insert agent configurations."""
        query = """
        INSERT INTO agents 
        (session_id, agent_label, provider, model, temperature, persona, system_prompt)
        VALUES (%s, %s, %s, %s, %s, %s, %s)
        ON DUPLICATE KEY UPDATE
        provider = VALUES(provider),
        model = VALUES(model),
        temperature = VALUES(temperature),
        persona = VALUES(persona),
        system_prompt = VALUES(system_prompt)
        """
        
        try:
            for agent in agents:
                self.cursor.execute(query, (
                    session_id,
                    agent['label'],
                    agent['provider'],
                    agent['model'],
                    agent['temperature'],
                    agent['persona'],
                    agent['system_prompt']
                ))
            return True
        except mysql.connector.Error as e:
            print(f"Error inserting agents: {e}")
            return False
    
    def insert_messages(self, session_id: str, messages: List[Dict]) -> bool:
        """Insert conversation messages."""
        query = """
        INSERT INTO messages 
        (session_id, speaker, timestamp, content, message_order)
        VALUES (%s, %s, %s, %s, %s)
        """
        
        try:
            for msg in messages:
                self.cursor.execute(query, (
                    session_id,
                    msg['speaker'],
                    msg['timestamp'],
                    msg['content'],
                    msg['order']
                ))
            return True
        except mysql.connector.Error as e:
            print(f"Error inserting messages: {e}")
            return False
    
    def process_file(self, filepath: str) -> bool:
        """Process a single markdown file and insert into database."""
        print(f"Processing: {filepath}")

        try:
            # Parse the file
            data = self.parse_markdown_file(filepath)

            # Connect to database
            self.connect()

            # Insert session
            if not self.insert_session(data['session']):
                return False

            # Insert agents
            if not self.insert_agents(data['session']['session_id'], data['agents']):
                return False

#             # Insert messages
#             if not self.insert_messages(data['session']['session_id'], data['messages']):
#                 return False
# 
            # Commit transaction
            self.conn.commit()
            print(f"Successfully processed: {filepath}")

            # Archive the log file after successful processing
           # if not self.archive_log(filepath):
            #    print(f"Warning: Failed to archive {filepath}")

            return True

        except Exception as e:
            print(f"Error processing file {filepath}: {e}")
            if self.conn:
                self.conn.rollback()
            return False
        finally:
            self.disconnect()
    
    def process_directory(self, directory: str, pattern: str = "*.md"):
        """Process all markdown files in a directory."""
        path = Path(directory)
        files = list(path.glob(pattern))
    
        # Filter out logging.md and index.md
        files = [f for f in files if f.name not in ['logging.md', 'index.md']]
        
        print(f"Found {len(files)} files to process")
        
        success_count = 0
        for filepath in files:
            if self.process_file(str(filepath)):
                success_count += 1
        
        print(f"\nProcessed {success_count}/{len(files)} files successfully")


# Usage example
if __name__ == "__main__":
    import os
    from dotenv import load_dotenv
    
    # Load environment variables
    load_dotenv()
    
    # Database configuration from .env
    db_config = {
        'host': os.getenv('DB_HOST', 'localhost'),
        'user': os.getenv('DB_USER'),
        'password': os.getenv('DB_PASSWORD'),
        'database': os.getenv('DB_NAME'),
        'port': int(os.getenv('DB_PORT', 3306))
    }
    
    # Create processor
    processor = ChatBridgeProcessor(db_config)
    
    # Process all markdown files in current directory
    processor.process_directory('.')
    
    # Or specify a different directory:
    # processor.process_directory('/path/to/transcripts')
