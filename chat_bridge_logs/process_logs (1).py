import re
import mysql.connector
import zipfile
import shutil
from datetime import datetime
from pathlib import Path
from typing import Dict, List, Optional
import os

class ChatBridgeProcessor:
    def __init__(self, db_config: Dict[str, str], archive_folder: str = 'archive'):
        self.db_config = db_config
        self.conn = None
        self.cursor = None
        self.archive_folder = Path(archive_folder)
    
    def connect(self):
        self.conn = mysql.connector.connect(**self.db_config)
        self.cursor = self.conn.cursor()
    
    def disconnect(self):
        if self.cursor:
            self.cursor.close()
        if self.conn:
            self.conn.close()

    def archive_log(self, filepath: str) -> bool:
        try:
            filepath = Path(filepath)
            self.archive_folder.mkdir(exist_ok=True)
            timestamp = datetime.now().strftime('%Y%m%d')
            zip_filename = f"{filepath.stem}_{timestamp}.zip"
            zip_path = self.archive_folder / zip_filename

            with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
                zipf.write(filepath, filepath.name)

            # SAFETY UPDATE: Commented out deletion so files remain for the viewer
            # filepath.unlink() 

            print(f"Archived: {filepath.name} -> {zip_path}")
            return True

        except Exception as e:
            print(f"Error archiving file {filepath}: {e}")
            return False
    
    def parse_markdown_file(self, filepath: str) -> Dict:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        data = {
            'session': {},
            'agents': [],
            'messages': []
        }
        
        # Regex patterns
        session_id_match = re.search(r'\*\*Session ID:\*\* (conv_\d+_\d+)', content)
        started_match = re.search(r'\*\*Started:\*\* ([\d-]+ [\d:]+)', content)
        starter_match = re.search(r'\*\*Conversation Starter:\*\* (.+?)(?=\n\n|\n###)', content, re.DOTALL)
        max_rounds_match = re.search(r'\*\*Max Rounds:\*\* (\d+)', content)
        memory_rounds_match = re.search(r'\*\*Memory Rounds:\*\* (\d+)', content)
        stop_word_match = re.search(r'\*\*Stop Word Detection:\*\* (\w+)', content)
        stop_words_match = re.search(r'\*\*Stop Words:\*\* (.+)', content)
        
        data['session']['session_id'] = session_id_match.group(1) if session_id_match else None
        data['session']['started_at'] = started_match.group(1) if started_match else None
        data['session']['conversation_starter'] = starter_match.group(1).strip() if starter_match else None
        data['session']['max_rounds'] = int(max_rounds_match.group(1)) if max_rounds_match else 0
        data['session']['memory_rounds'] = int(memory_rounds_match.group(1)) if memory_rounds_match else 0
        data['session']['stop_word_detection'] = stop_word_match.group(1) == 'Enabled' if stop_word_match else False
        data['session']['stop_words'] = stop_words_match.group(1) if stop_words_match else None
        
        # Agent A
        agent_a = {
            'label': 'A',
            'provider': self._extract_field(content, 'Agent A Provider'),
            'model': self._extract_field(content, 'Agent A Model'),
            'temperature': self._extract_float(content, 'Agent A Temperature'),
            'persona': self._extract_field(content, 'Agent A Persona'),
            'system_prompt': self._extract_multiline_field(content, 'Agent A System Prompt')
        }
        data['agents'].append(agent_a)
        
        # Agent B
        agent_b = {
            'label': 'B',
            'provider': self._extract_field(content, 'Agent B Provider'),
            'model': self._extract_field(content, 'Agent B Model'),
            'temperature': self._extract_float(content, 'Agent B Temperature'),
            'persona': self._extract_field(content, 'Agent B Persona'),
            'system_prompt': self._extract_multiline_field(content, 'Agent B System Prompt')
        }
        data['agents'].append(agent_b)
        
        # Messages
        conversation_section = re.search(r'## Conversation\n\n(.+)', content, re.DOTALL)
        if conversation_section:
            messages_text = conversation_section.group(1)
            message_pattern = r'### (Human|Agent A|Agent B) \(([\d-]+ [\d:]+)\)\n(?:<sub[^>]*>.*?</sub>\n)?\n?(.+?)(?=\n### |\Z)'

            for i, match in enumerate(re.finditer(message_pattern, messages_text, re.DOTALL)):
                speaker = match.group(1)
                timestamp = match.group(2)
                message_content = match.group(3).strip()
                # Clean hidden HTML tags
                message_content = re.sub(r'^<sub[^>]*>.*?</sub>\s*', '', message_content, flags=re.DOTALL)

                data['messages'].append({
                    'speaker': speaker,
                    'timestamp': timestamp,
                    'content': message_content,
                    'order': i
                })
        
        return data
    
    def _extract_field(self, content: str, field_name: str) -> Optional[str]:
        match = re.search(rf'\*\*{field_name}:\*\* (.+)', content)
        return match.group(1).strip() if match else None
    
    def _extract_float(self, content: str, field_name: str) -> Optional[float]:
        match = re.search(rf'\*\*{field_name}:\*\* ([\d.]+)', content)
        return float(match.group(1)) if match else 0.0
    
    def _extract_multiline_field(self, content: str, field_name: str) -> Optional[str]:
        match = re.search(rf'\*\*{field_name}:\*\* (.+?)(?=\n\n\*\*|\n###)', content, re.DOTALL)
        return match.group(1).strip() if match else None
    
    def insert_session(self, session_data: Dict) -> bool:
        if not session_data.get('session_id'): return False
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
        query = """
        INSERT INTO agents 
        (session_id, agent_label, provider, model, temperature, persona, system_prompt)
        VALUES (%s, %s, %s, %s, %s, %s, %s)
        ON DUPLICATE KEY UPDATE
        provider = VALUES(provider),
        model = VALUES(model)
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
        query = """
        INSERT INTO messages 
        (session_id, speaker, timestamp, content, message_order)
        VALUES (%s, %s, %s, %s, %s)
        ON DUPLICATE KEY UPDATE content = VALUES(content)
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
        print(f"Processing: {filepath}")
        try:
            data = self.parse_markdown_file(filepath)
            self.connect()

            if not self.insert_session(data['session']): return False
            if not self.insert_agents(data['session']['session_id'], data['agents']): return False
            
            # FIXED: Uncommented this line to actually save messages
            if not self.insert_messages(data['session']['session_id'], data['messages']): return False

            self.conn.commit()
            print(f"Successfully processed: {filepath}")
            return True

        except Exception as e:
            print(f"Error processing file {filepath}: {e}")
            if self.conn: self.conn.rollback()
            return False
        finally:
            self.disconnect()
    
    def process_directory(self, directory: str, pattern: str = "*.md"):
        path = Path(directory)
        files = list(path.glob(pattern))
        files = [f for f in files if f.name not in ['logging.md', 'index.md']]
        
        print(f"Found {len(files)} files to process")
        count = 0
        for filepath in files:
            if self.process_file(str(filepath)):
                count += 1
        print(f"\nProcessed {count}/{len(files)} files successfully")

if __name__ == "__main__":
    from dotenv import load_dotenv
    load_dotenv()
    
    db_config = {
        'host': os.getenv('DB_HOST', 'localhost'),
        'user': os.getenv('DB_USER'),
        'password': os.getenv('DB_PASSWORD'),
        'database': os.getenv('DB_NAME'),
        'port': int(os.getenv('DB_PORT', 3306))
    }
    
    processor = ChatBridgeProcessor(db_config)
    processor.process_directory('.')