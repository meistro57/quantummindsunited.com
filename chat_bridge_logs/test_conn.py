import os
import mysql.connector
from dotenv import load_dotenv

def test_database_connection():
    """Test database connection using .env configuration."""
    
    # Load environment variables
    load_dotenv()
    
    # Get database config from .env
    db_config = {
        'host': os.getenv('DB_HOST', 'localhost'),
        'user': os.getenv('DB_USER'),
        'password': os.getenv('DB_PASSWORD'),
        'database': os.getenv('DB_NAME'),
        'port': int(os.getenv('DB_PORT', 3306))
    }
    
    print("Testing database connection...")
    print(f"Host: {db_config['host']}")
    print(f"Port: {db_config['port']}")
    print(f"User: {db_config['user']}")
    print(f"Database: {db_config['database']}")
    print("-" * 50)
    
    # Check for missing credentials
    if not db_config['user'] or not db_config['password']:
        print("❌ Error: DB_USER or DB_PASSWORD not found in .env file")
        return False
    
    try:
        # Attempt connection
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor()
        
        # Test query
        cursor.execute("SELECT VERSION()")
        version = cursor.fetchone()
        
        print(f"✅ Connection successful!")
        print(f"MySQL version: {version[0]}")
        
        # Check if tables exist
        cursor.execute("SHOW TABLES")
        tables = cursor.fetchall()
        
        if tables:
            print(f"\nExisting tables:")
            for table in tables:
                print(f"  - {table[0]}")
        else:
            print("\n⚠️  No tables found. You may need to run the CREATE TABLE statements.")
        
        cursor.close()
        conn.close()
        return True
        
    except mysql.connector.Error as e:
        print(f"❌ Connection failed: {e}")
        
        if e.errno == 1045:
            print("\nHint: Check your DB_USER and DB_PASSWORD in .env")
        elif e.errno == 2003:
            print("\nHint: Check if MySQL is running and DB_HOST/DB_PORT are correct")
        elif e.errno == 1049:
            print(f"\nHint: Database '{db_config['database']}' doesn't exist. Create it first:")
            print(f"      CREATE DATABASE {db_config['database']};")
        
        return False

if __name__ == "__main__":
    test_database_connection()
