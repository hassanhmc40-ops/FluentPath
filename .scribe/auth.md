# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {YOUR_TOKEN}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Get a token by calling `POST /api/register` (new account) or `POST /api/login`, then send it as `Authorization: Bearer {token}`.
