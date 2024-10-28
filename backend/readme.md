# Project Setup

## Prerequisites

1. **Create an Environment Template**  
   Create a file named `.env.template` for your environment variables. This file should not be versioned. It will be used to dynamically translate values into the `.env` file.

2. **Environment Variables**  
   Set the following environment variables in your system:
   - `WEALTH_TIMESCALEDB_VOLUME`: Path for TimescaleDB volume mapping
   - `WEALTH_DB_USERNAME`: Database username
   - `WEALTH_DB_PASSWORD`: Database password
   - `WEALTH_REDIS_VOLUME`: Path for Redis volume mapping 

3. **Run Application**  
   After configuring the `.env` file, start the application with Docker:
   ```bash
   docker-compose up -d
