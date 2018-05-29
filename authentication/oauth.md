# OAuth

{% api-method method="post" host="" path="/oauth/token" %}
{% api-method-summary %}
Client Authentication
{% endapi-method-summary %}

{% api-method-description %}
This endpoint will generate a new access token for your client
{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-form-data-parameters %}
{% api-method-parameter name="grant\_type" type="string" required=true %}
Should be set to \`client\_credentials\`
{% endapi-method-parameter %}

{% api-method-parameter name="client\_secret" type="string" required=true %}
The OAuth Client secret
{% endapi-method-parameter %}

{% api-method-parameter name="client\_id" type="string" required=true %}
The ID of the OAuth Client
{% endapi-method-parameter %}
{% endapi-method-form-data-parameters %}
{% endapi-method-request %}

{% api-method-response %}
{% api-method-response-example httpCode=200 %}
{% api-method-response-example-description %}

{% endapi-method-response-example-description %}

```javascript
{
    "token_type": "Bearer",
    "expires_in": 3600,
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
}
```
{% endapi-method-response-example %}
{% endapi-method-response %}
{% endapi-method-spec %}
{% endapi-method %}

{% api-method method="post" host="" path="/oauth/token" %}
{% api-method-summary %}
User Authentication
{% endapi-method-summary %}

{% api-method-description %}

{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-form-data-parameters %}
{% api-method-parameter name="username" type="string" required=true %}
The users Username
{% endapi-method-parameter %}

{% api-method-parameter name="password" type="string" required=true %}
The users password
{% endapi-method-parameter %}

{% api-method-parameter name="client\_id" type="string" required=true %}
The ID of the OAuth Client
{% endapi-method-parameter %}

{% api-method-parameter name="client\_secret" type="string" required=true %}
The OAuth Client secret
{% endapi-method-parameter %}

{% api-method-parameter name="grant\_type" type="string" required=true %}
Should be set to \`client\_credentials\`
{% endapi-method-parameter %}
{% endapi-method-form-data-parameters %}
{% endapi-method-request %}

{% api-method-response %}
{% api-method-response-example httpCode=200 %}
{% api-method-response-example-description %}

{% endapi-method-response-example-description %}

```javascript
{
    "token_type": "Bearer",
    "expires_in": 3600,
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
    "refresh_token": "def502214441d152354eef..."
}
```
{% endapi-method-response-example %}
{% endapi-method-response %}
{% endapi-method-spec %}
{% endapi-method %}

{% api-method method="get" host="" path="/users/current" %}
{% api-method-summary %}
Current User
{% endapi-method-summary %}

{% api-method-description %}

{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-path-parameters %}
{% api-method-parameter name="includes" type="string" required=false %}
Define any user relation includes
{% endapi-method-parameter %}
{% endapi-method-path-parameters %}
{% endapi-method-request %}

{% api-method-response %}
{% api-method-response-example httpCode=200 %}
{% api-method-response-example-description %}

{% endapi-method-response-example-description %}

```javascript
{
    "data":{
        "id":"4xqlnwvd",
        "email":"shaun@shaunrainer.com"
    }
    "meta":{
        "lang":"en"
    }
}
```
{% endapi-method-response-example %}
{% endapi-method-response %}
{% endapi-method-spec %}
{% endapi-method %}

### Available Includes

| addresses | Include the users addresses |
| --- | --- | --- | --- |
| details | Include the users details |
| groups | Include the users custom groups |
| orders | include the users orders |

{% api-method method="post" host="" path="/password/reset/request" %}
{% api-method-summary %}
Request Password Reset
{% endapi-method-summary %}

{% api-method-description %}

{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-query-parameters %}
{% api-method-parameter name="email" type="string" required=true %}
The email address to reset
{% endapi-method-parameter %}
{% endapi-method-query-parameters %}
{% endapi-method-request %}

{% api-method-response %}
{% api-method-response-example httpCode=200 %}
{% api-method-response-example-description %}

{% endapi-method-response-example-description %}

```

```
{% endapi-method-response-example %}
{% endapi-method-response %}
{% endapi-method-spec %}
{% endapi-method %}

{% api-method method="get" host="" path="" %}
{% api-method-summary %}
Reset the Password
{% endapi-method-summary %}

{% api-method-description %}

{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-query-parameters %}
{% api-method-parameter name="email" type="string" required=true %}
The email used in the reset request
{% endapi-method-parameter %}

{% api-method-parameter name="password" type="string" required=true %}
The new password
{% endapi-method-parameter %}

{% api-method-parameter name="password\_confirmation" type="string" required=true %}
Confirmation of the new password
{% endapi-method-parameter %}

{% api-method-parameter name="\_token" type="string" required=true %}
The token given by the reset request
{% endapi-method-parameter %}
{% endapi-method-query-parameters %}
{% endapi-method-request %}

{% api-method-response %}
{% api-method-response-example httpCode=200 %}
{% api-method-response-example-description %}

{% endapi-method-response-example-description %}

```

```
{% endapi-method-response-example %}
{% endapi-method-response %}
{% endapi-method-spec %}
{% endapi-method %}



