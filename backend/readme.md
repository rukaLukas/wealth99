[Insomnia-All_2024-10-28.json](https://github.com/user-attachments/files/17538961/Insomnia-All_2024-10-28.json)# Project Setup

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

Application will listen on port **8181**

### Tests
- Insider container run
  ```bash
  ./vendor/bin/phpunit
  

### Additional Notes
- You might want to provide details about setting up testing configurations or dependencies if they are necessary for running the tests.
- If you have specific testing commands or configurations (like running tests for specific directories or using coverage reports), you can include those as well for enhanced usability.
- Ensure users know how to view logs or troubleshoot any issues that arise during testing.


### Overview Description Architecture

# Architecture Overview

This application utilizes an **event-driven, queue-based architecture** to manage requests for cryptocurrency prices from the CoinGecko API. The primary goal is to accommodate API rate limit constraints while ensuring timely access to price data.

### API Rate Limiting Strategy
The architecture was designed to comply with CoinGecko’s rate limits by orchestrating requests for both current and historical prices using two distinct strategies:
- **Recent Prices**: For fast access to current prices.
- **Historical Data**: Queries historical prices by starting with shorter date intervals and progressively expanding the range until data is found. This lazy-loading approach minimizes API requests and optimizes rate limit usage.

> **Note**: If a broader rate limit were available through an API key, alternative strategies could be implemented for enhanced performance.

### Database Choice: TimescaleDB
This application leverages **TimescaleDB**, a PostgreSQL extension specifically designed for time-series data. TimescaleDB was selected for its efficient handling of time-based datasets, making it ideal for storing and querying large volumes of cryptocurrency prices over time. Key benefits include:
- **Efficient Date Range Queries**: Optimized for large, time-series datasets.
- **Scalability**: Supports the scale and performance demands of time-based price tracking.

### Future Improvements
To further enhance system performance, **queue chaining** can be implemented to manage task prioritization. By prioritizing high-frequency tasks (such as fetching recent prices) over longer, historical queries, we can reduce wait times and improve response times for critical data retrievals. This ensures that recent data requests are not delayed by lower-priority tasks, enhancing the application’s overall efficiency.

---

This documentation provides an architectural overview for contributors and collaborators, explaining the reasoning behind core design decisions and potential areas for future optimization.



### Endpoints
[Insomnia-All_2024-10-28.zip](https://github.com/user-attachments/files/17539010/Insomnia-All_2024-10-28.zip)



