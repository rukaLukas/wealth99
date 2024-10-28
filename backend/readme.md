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
[Uploading Insomnia-All_2024-10-2{"_type":"export","__export_format":4,"__export_date":"2024-10-28T07:22:26.354Z","__export_source":"insomnia.desktop.app:v2023.2.1","resources":[{"_id":"req_ef7700be8a1941509020d9325530d0e4","parentId":"wrk_a865c5ec377f473c86a3dd809d6e3798","modified":1730100104358,"created":1730098870971,"url":"ec2-18-234-87-125.compute-1.amazonaws.com:8181/api/v1/prices","name":"prod_recents","description":"","method":"GET","body":{},"parameters":[],"headers":[{"id":"pair_f8fd3cc81193436e9b18bdefbe885c1c","name":"Content-type","value":"application/json","description":""},{"id":"pair_c9e5d330d6174ec0960a779ffb3ba276","name":"Accept","value":"application/json","description":""}],"authentication":{},"metaSortKey":-1730098870971,"isPrivate":false,"settingStoreCookies":true,"settingSendCookies":true,"settingDisableRenderRequestBody":false,"settingEncodeUrl":true,"settingRebuildPath":true,"settingFollowRedirects":"global","_type":"request"},{"_id":"wrk_a865c5ec377f473c86a3dd809d6e3798","parentId":null,"modified":1729750687890,"created":1729750687890,"name":"My Collection","description":"","scope":"collection","_type":"workspace"},{"_id":"req_e214fa42128e4d6f88e96add04ed1bf1","parentId":"wrk_a865c5ec377f473c86a3dd809d6e3798","modified":1730100068275,"created":1730098912948,"url":"ec2-18-234-87-125.compute-1.amazonaws.com:8181/api/v1/prices/2024-10-02 10:10:00","name":"prod_by_date","description":"","method":"GET","body":{},"parameters":[],"headers":[{"id":"pair_463a8b83b2334660810f8b5ef3c07b96","name":"Content-type","value":"application/json","description":"","disabled":false},{"id":"pair_86ab3edb30fb476bab7b6a65d4cf15a1","name":"Accept","value":"application/json","description":"","disabled":false}],"authentication":{},"metaSortKey":-1729782529293,"isPrivate":false,"settingStoreCookies":true,"settingSendCookies":true,"settingDisableRenderRequestBody":false,"settingEncodeUrl":true,"settingRebuildPath":true,"settingFollowRedirects":"global","_type":"request"},{"_id":"env_45020ac618527f57efd3e1ff688f12c671f200d7","parentId":"wrk_a865c5ec377f473c86a3dd809d6e3798","modified":1729750687894,"created":1729750687894,"name":"Base Environment","data":{},"dataPropertyOrder":null,"color":null,"isPrivate":false,"metaSortKey":1729750687894,"_type":"environment"},{"_id":"jar_45020ac618527f57efd3e1ff688f12c671f200d7","parentId":"wrk_a865c5ec377f473c86a3dd809d6e3798","modified":1730036239856,"created":1729750687895,"name":"Default Jar","cookies":[{"key":"XSRF-TOKEN","value":"eyJpdiI6IlhwZGNhOFhseUU5VElzS0czU282XC9nPT0iLCJ2YWx1ZSI6IjhZZ1FGejdMZDhGUVh2czluRld0QStlalBIbFFrbW1aNFptNmxNTzlxSWx0OXVQdURYWUg5VVc5TTZyZFlHTzciLCJtYWMiOiI1OGRiYTMxMGFiZjllZDljMTI1Y2JkZmE5Y2EyNTFjNDNiZGUzNmIxZjhlNmVmZmNhNDAzYWMzYWI2MGRkNDQ4In0%3D","expires":"2024-10-26T22:12:53.000Z","maxAge":7200,"domain":"localhost","path":"/","hostOnly":true,"creation":"2024-10-24T06:18:21.040Z","lastAccessed":"2024-10-26T20:12:53.060Z","id":"6430979561210681"},{"key":"laravel_session","value":"eyJpdiI6IkRadkFsZ0lLcVR5N2x6R3VtUzNWamc9PSIsInZhbHVlIjoib0ZlK3dyb3BsSkIrTmVwUzRpSkc4VzdnSlZIdE1pcXJ5cmxBK1wvZUZJeFRqREhlWFBoaGNVZ2I0Wm44NlwvUzRlIiwibWFjIjoiNDYxNWZhZjFhODA0NWEwYjlkNzAxNjc4YTc1NzdhNDA4MDFlNzVlMThjNjFhMmEzMjcyYzA3Njg4MWE3OGJmYSJ9","expires":"2024-10-26T22:12:53.000Z","maxAge":7200,"domain":"localhost","path":"/","httpOnly":true,"hostOnly":true,"creation":"2024-10-24T06:18:21.041Z","lastAccessed":"2024-10-26T20:12:53.060Z","id":"35774718697414865"},{"key":"__cf_bm","value":"xbSPhJbK_YWX2RwKhkjhRMecCiS.AvNPeQAKEztX1yU-1730036239-1.0.1.1-g1aJzEjV_XQ1UfdtWvXr52E6npkLFbwc59BpDpPpPtpvqKKxvjK_ro3Bo4mxvUnzywHl1njGw_CMNIvMaXQ25g","expires":"2024-10-27T14:07:19.000Z","domain":"api.coingecko.com","path":"/","secure":true,"httpOnly":true,"extensions":["SameSite=None"],"hostOnly":false,"creation":"2024-10-27T13:37:19.854Z","lastAccessed":"2024-10-27T13:37:19.854Z","id":"6945308814683355"}],"_type":"cookie_jar"},{"_id":"spc_79b1b879c58a45a4890c52064094b44f","parentId":"wrk_a865c5ec377f473c86a3dd809d6e3798","modified":1729750687891,"created":1729750687891,"fileName":"My Collection","contents":"","contentType":"yaml","_type":"api_spec"}]}8.json…]()



