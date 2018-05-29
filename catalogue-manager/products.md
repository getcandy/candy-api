# Products

{% api-method method="get" host="" path="/api/v1/products" %}
{% api-method-summary %}
Get a list of products
{% endapi-method-summary %}

{% api-method-description %}

{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-headers %}
{% api-method-parameter name="Authorization" type="string" required=true %}
Bearer &lt;token&gt;
{% endapi-method-parameter %}
{% endapi-method-headers %}

{% api-method-query-parameters %}
{% api-method-parameter name="includes" type="string" required=false %}
Define any product relation includes
{% endapi-method-parameter %}
{% endapi-method-query-parameters %}
{% endapi-method-request %}

{% api-method-response %}
{% api-method-response-example httpCode=200 %}
{% api-method-response-example-description %}

{% endapi-method-response-example-description %}

```
{
  "data": [
    {
      "id": "xojxzgy5",
      "attribute_data": {
        "name": {
          "webstore": {
            "en": "ENUFF CLASSIC FADE"
          }
        },
        "description": {
          "webstore": {
            "en": "<p style=\"font-family: &quot;Open Sans&quot;, sans-serif; font-size: 13px;\">Brand new from Enuff, is this awesome looking new Fade deck. Available in three different sizes so you get your perfect width. Check the spec:<\/p><p style=\"font-family: &quot;Open Sans&quot;, sans-serif; font-size: 13px;\">Sizes: 32\u201d x 8.00\u2033, 32.125 x 8.125\u2033 and 32.125\u201d x 8.25\u2033<\/p><p style=\"font-family: &quot;Open Sans&quot;, sans-serif; font-size: 13px;\">Concave: Medium Concave deck<\/p><p style=\"font-family: &quot;Open Sans&quot;, sans-serif; font-size: 13px;\">Construction: 100% Canadian Maple. 7 Ply Cold Pressed. Gloss varnish with three stained ply bottom ply is Two Tone Stain Finish<\/p><p style=\"font-family: &quot;Open Sans&quot;, sans-serif; font-size: 13px;\">If you would like to apply the FREE GRIPTAPE yourself, please contact us after purchasing this deck<\/p>"
          }
        },
        "short_description": {
          "webstore": {
            "en": "<p><span style=\"font-family: &quot;Open Sans&quot;, sans-serif; font-size: 13px;\">Brand new from Enuff, is this awesome looking new Fade deck. Available in three different sizes so you get your perfect width<\/span><\/p>"
          }
        }
      },
      "option_data": [],
      "thumbnail": null,
      "max_price": 25,
      "min_price": 25,
      "variant_count": 1
    }
  ],
  "meta": {
    "lang": "en",
    "pagination": {
      "total": 1,
      "count": 1,
      "per_page": 15,
      "current_page": 1,
      "total_pages": 1,
      "links": []
    }
  }
}
```
{% endapi-method-response-example %}
{% endapi-method-response %}
{% endapi-method-spec %}
{% endapi-method %}

{% api-method method="get" host="" path="/api/v1/product/:hashed-id" %}
{% api-method-summary %}
Get a single product
{% endapi-method-summary %}

{% api-method-description %}

{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-path-parameters %}
{% api-method-parameter name="id" type="string" required=false %}
The products hashed ID
{% endapi-method-parameter %}
{% endapi-method-path-parameters %}
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

{% api-method method="post" host="" path="/api/v1/products" %}
{% api-method-summary %}
Create a new product
{% endapi-method-summary %}

{% api-method-description %}

{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-query-parameters %}
{% api-method-parameter name="layout\_id" type="string" required=false %}
The hashed layout id
{% endapi-method-parameter %}

{% api-method-parameter name="sku" type="string" required=true %}
Product stock keeping unit
{% endapi-method-parameter %}

{% api-method-parameter name="family\_id" type="string" required=true %}
The hashed family id
{% endapi-method-parameter %}

{% api-method-parameter name="attributes\[name\]\[channel\]\[language\]" type="string" required=false %}
Array of attributes associated with product
{% endapi-method-parameter %}

{% api-method-parameter name="name\[language\]" type="string" required=true %}
Define a product name
{% endapi-method-parameter %}
{% endapi-method-query-parameters %}
{% endapi-method-request %}

{% api-method-response %}
{% api-method-response-example httpCode=200 %}
{% api-method-response-example-description %}

{% endapi-method-response-example-description %}

```javascript
{
    "data": {
        "id": "8jv74o1j",
        "attribute_data": {
            "name": {
                "channel": {
                    "language": "Test Product"
                }
            }
        },
        "option_data": [],
        "thumbnail": null,
        "max_price": 123,
        "min_price": 123,
        "variant_count": 1
    },
    "meta":{
        "lang":"en"
    }
}
```
{% endapi-method-response-example %}
{% endapi-method-response %}
{% endapi-method-spec %}
{% endapi-method %}

{% api-method method="get" host="" path="/api/v1/products/:id/assets" %}
{% api-method-summary %}
Add Asset to a Product
{% endapi-method-summary %}

{% api-method-description %}

{% endapi-method-description %}

{% api-method-spec %}
{% api-method-request %}
{% api-method-body-parameters %}
{% api-method-parameter name="includes" type="string" required=false %}

{% endapi-method-parameter %}

{% api-method-parameter name="url" type="string" required=false %}
Using external mime\_type then this is the url of image
{% endapi-method-parameter %}

{% api-method-parameter name="mime\_type" type="string" required=true %}
Type of asset being added e.g. image, external...
{% endapi-method-parameter %}

{% api-method-parameter name="file" type="object" required=false %}
Multipart file upload
{% endapi-method-parameter %}
{% endapi-method-body-parameters %}
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

### Available Mime Types

| image |  |
| --- | --- | --- | --- |
| file |  |
| video |  |
| external |  |

{% code-tabs %}
{% code-tabs-item title="test php" %}
```ruby
dsfdsfsd
```
{% endcode-tabs-item %}

{% code-tabs-item title="ruby test" %}
```


```
{% endcode-tabs-item %}
{% endcode-tabs %}



