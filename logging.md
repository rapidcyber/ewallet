# Logging format

## What are logs and where will it be used?
- Logs are a way to audit the actions or changes in an event for a specific module
- As of 08/22/2024, modules that should have logs are:
    - ProductOrder
    - LalamoveOrder
    - ReturnOrder
    - Invoice
    - Admin Actions

## Implementation
- Each module will have its own logs table
- Format of each logs table will vary depending if the module will depend on a status from a lookup table or not
- Log tables that depend on statuses only need to include the new status for each log entry
- Some log tables will only have a text
- In log tables having text:
    1. Include the module
    2. Include the action that happened
- E.g. AdminLogs - `username` has `approved` `Product` with id `3`
- E.g. AdminLogs - `username` has `suspended` `User` with id `5`