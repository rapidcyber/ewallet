### REPAY WEB

# Development

## IMPORTANT

-   Current integration of webhooks meant for external use must be defined exactly as before.

    -   Web routing
    -   API endpoints

-   Installation of third party packages

-   Preparation of the following:

    -   Passport configuration
        -   for API consuption of mobile app and external integrations.

-   Scaffolding of folder structure

## CODE COMMITS

-   NO PUSHING BRANCH ZONES
    -   `main`
    -   `beta`
    -   `alpha`

## App Constants

-   add app constants to file `config\app_constants.php`.

```php
return [
    'errors' => [
        '[error_key]' => [
            'message' => '[error message]',
            'code' => 0, // message status code
        ],
    ],
    'messages' => [
        '[message_key]' => '[message_string]',
    ]
];
```
