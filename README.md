# Unified Subscription system



### 1. Login
```
Header : must include appkey and bearer token
Method : Post
Link : /api/auth/login
```
**_Parameters_**
```json
{
    "email": 'xxx@xxxx.xxx',
    "pasword": 'Kenya@60!',
    
}
```
**_Successful Response_**
```json
{
    "token": "tyyyy6h7j7867j768u76y67hy67yu67u67yh67yh67y67wGm_ujrYCIcJ4UyQKR3eEw9X5dkYSeYBcrv6giaTgwZpU46x0N6op97kq2jwg76v9TcWn6ov7bZi0pz3YdxrPIxbWPqg4"
}
```
**_Failed Response_**
```json
{
    "message": "Password mismatch"
}
```
or when using the wrong appkey
```json 
{
    "status": false,
    "error": "Invalid token"
}
```
or when appkey missing
```json
{
    "status": false,
    "error": "Unauthorized action"
}
```
### 2. Register
```
Header : must include appkey
Method : Post
Link : /api/auth/register
```
**_Parameters_**
```json
{
    "name":"jon Doe",
    "email":"john.doe@xxxxx.xxx",
    "password":"pass12345!",
    "password_confirmation":"pass12345!"
}
```
**_Response_**
```json
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMTEyMDk5OTYxNGY2ODEyNTU1ZmU1MTM1MGQwMDllMmJjMDNhNzBkMDNmZjEzMTczMGE4NTkxYmFmNDI4NGRkZjFkMTBmYTcwZmM1MTMyNDUiLCJpYXQiOjE2NzMzNTE1NDcuNjg0NjYzLCJuYmYiOjE2NzMzNTE1NDcuNjg0NjY2LCJleHAiOjE2ODg5ODk5NDcuNjgyMDI3LCJzdWIiOiIzIiwic2NvcGVzIjpbXX0.Es7vNemhDE5BRGSBIqORYADxbx4_gmVpVxMh_omAW8Z-BI9iwbDN6DBt8tlmdEb5SspUtKeoFN1ZmuedQyglU3-yDnYz7LV_iqiRv_9CAecpRusajjh-5u9_hSds9oVVGjSxqzyetzpyCNBBjR5wbdtn6IqM0_EEhR8aiRLMM-WfTRg78wOWe2KnL6Yl8yuA2WacaqoT3N7k26plD1ZzgSMYWTkRqbzonGEFnRRgn0OXRe7xkyoHz4JJIjDREzjZ-3x6tFZAGouTx-sI0O21sIlPFlR9JFofoky9anMlGAfbnFobrP9BfxeJsAZER3w92zOMT-Vho6AxpWQYr-jCEnCEnwx9HjfM-G08OSDM1hl22mHvRY99iqQ8_CrI1k52qWnv7pDL8y7znNeiZcvdjEXFI9Jyz4DQx8HGCIACoIDSrZYH8gKbPi5GCfDwD8aRUSoOlqTps6FEL5BzPXg89L8n-rUtfrRIPAWhHCiBsYn9LzJaJTvd0W50uriXFKzv7lHBVs3gKH2TDiPWPl7AJ5nBaHcGaustn-xu82AKY2AnaUQIEgsyb3GP73KmxWiRdUAOtVxrQ1H0ykoGWNfYE7_SQOk8TirrMDgPdzMUoX3UY1B7BHRz5PjW5f4tcmX6yTt1nxoQ1X68pZFpgRlII5q5LVbFzMzejAy_O_BXZBI"
}
```
### 4. Check User By ID
```
Header : must include appkey and bearer token
Method : Post
Link : /api/get_user_by_id
```
**_Parameters_**
```json
{
    "id":1
}
```
**_Response_**
```json
{
    "status": true,
    "data": {
        "id": 1,
        "username": "admin",
        "name": "Default Administrator",
        "email": "info@nation.africa",
        "email_verified_at": null,
        "organization_id": 0,
        "remember_token": null,
        "created_at": "2023-04-11T16:39:36.000000Z",
        "updated_at": "2023-05-07T11:59:22.000000Z"
    }
}
```
### 5. Check User By Email
```
Header : must include appkey and bearer token
Method : Post
Link : /api/get_user_by_email
```
**_Parameters_**
```json
{
    "email":"info@nation.africa"
}
```
**_Response_**
```json
{
    "status": true,
    "data": {
        "id": 1,
        "username": "admin",
        "name": "Default Administrator",
        "email": "info@nation.africa",
        "email_verified_at": null,
        "organization_id": 0,
        "remember_token": null,
        "created_at": "2023-04-11T16:39:36.000000Z",
        "updated_at": "2023-05-07T11:59:22.000000Z"
    }
}
```
### 6. Password Reset
```
Header : must include appkey
Method : Post
Link : /api/auth/passreset
```
**_Parameters_**
```json
{
    "email":"dennis.kiptoo@caydeesoft.org",
    "product_id":1
}
```
**_Response_**
```json
{
    "status": true,
    "data": {
        "id": 1,
        "username": "admin",
        "name": "Default Administrator",
        "email": "info@nation.africa",
        "email_verified_at": null,
        "organization_id": 0,
        "remember_token": null,
        "created_at": "2023-04-11T16:39:36.000000Z",
        "updated_at": "2023-05-07T11:59:22.000000Z"
    }
}
```

### 7. Logout
```
Header : must include appkey and bearer token
Method : Post
Link : /api/auth/passreset
```

**_Response_**
```json
{
    "status": true,
    "data": {
        "message": "You have been successfully logged out!"
    }
}
```

### 8. Regions
```
Header : must include appkey and bearer token
Method : Get
Link : /api/get_regions
```
**_Parameters_**
```json
{
    "start":0,
    "end":10,
    "orderBy":"code",
    "orderFormat":"ASC"
}
```
**_Response_**
```json
{
    "status": true,
    "data": [
        {
            "id": 6,
            "name": "Andorra",
            "code": "AD",
            "capital": "Andorra la Vella",
            "currency": "Euro",
            "currency_code": "EUR",
            "currency_symbol": "€",
            "language": "Catalan",
            "language_code": "ca",
            "flag": "https://restcountries.eu/data/and.svg",
            "mnos": null,
            "created_at": "2023-04-11T16:39:47.000000Z",
            "updated_at": "2023-04-11T16:39:47.000000Z"
        },
        {
            "id": 237,
            "name": "United Arab Emirates",
            "code": "AE",
            "capital": "Abu Dhabi",
            "currency": "United Arab Emirates dirham",
            "currency_code": "AED",
            "currency_symbol": "د.إ",
            "language": "Arabic",
            "language_code": "ar",
            "flag": "https://restcountries.eu/data/are.svg",
            "mnos": null,
            "created_at": "2023-04-11T16:39:48.000000Z",
            "updated_at": "2023-04-11T16:39:48.000000Z"
        },
        {
            "id": 1,
            "name": "Afghanistan",
            "code": "AF",
            "capital": "Kabul",
            "currency": "Afghan afghani",
            "currency_code": "AFN",
            "currency_symbol": "؋",
            "language": "Pashto",
            "language_code": "ps",
            "flag": "https://restcountries.eu/data/afg.svg",
            "mnos": null,
            "created_at": "2023-04-11T16:39:47.000000Z",
            "updated_at": "2023-04-11T16:39:47.000000Z"
        },
        {
            "id": 9,
            "name": "Antigua and Barbuda",
            "code": "AG",
            "capital": "Saint John's",
            "currency": "East Caribbean dollar",
            "currency_code": "XCD",
            "currency_symbol": "$",
            "language": "English",
            "language_code": "en",
            "flag": "https://restcountries.eu/data/atg.svg",
            "mnos": null,
            "created_at": "2023-04-11T16:39:47.000000Z",
            "updated_at": "2023-04-11T16:39:47.000000Z"
        },
        {
            "id": 8,
            "name": "Anguilla",
            "code": "AI",
            "capital": "The Valley",
            "currency": "East Caribbean dollar",
            "currency_code": "XCD",
            "currency_symbol": "$",
            "language": "English",
            "language_code": "en",
            "flag": "https://restcountries.eu/data/aia.svg",
            "mnos": null,
            "created_at": "2023-04-11T16:39:47.000000Z",
            "updated_at": "2023-04-11T16:39:47.000000Z"
        },
        {
            "id": 3,
            "name": "Albania",
            "code": "AL",
            "capital": "Tirana",
            "currency": "Albanian lek",
            "currency_code": "ALL",
            "currency_symbol": "L",
            "language": "Albanian",
            "language_code": "sq",
            "flag": "https://restcountries.eu/data/alb.svg",
            "mnos": null,
            "created_at": "2023-04-11T16:39:47.000000Z",
            "updated_at": "2023-04-11T16:39:47.000000Z"
        },
        {
            "id": 11,
            "name": "Armenia",
            "code": "AM",
            "capital": "Yerevan",
            "currency": "Armenian dram",
            "currency_code": "AMD",
            "currency_symbol": "",
            "language": "Armenian",
            "language_code": "hy",
            "flag": "https://restcountries.eu/data/arm.svg",
            "mnos": null,
            "created_at": "2023-04-11T16:39:47.000000Z",
            "updated_at": "2023-04-11T16:39:47.000000Z"
        },
        {
            "id": 7,
            "name": "Angola",
            "code": "AO",
            "capital": "Luanda",
            "currency": "Angolan kwanza",
            "currency_code": "AOA",
            "currency_symbol": "Kz",
            "language": "Portuguese",
            "language_code": "pt",
            "flag": "https://restcountries.eu/data/ago.svg",
            "mnos": null,
            "created_at": "2023-04-11T16:39:47.000000Z",
            "updated_at": "2023-04-11T16:39:47.000000Z"
        },
        {
            "id": 10,
            "name": "Argentina",
            "code": "AR",
            "capital": "Buenos Aires",
            "currency": "Argentine peso",
            "currency_code": "ARS",
            "currency_symbol": "$",
            "language": "Spanish",
            "language_code": "es",
            "flag": "https://restcountries.eu/data/arg.svg",
            "mnos": null,
            "created_at": "2023-04-11T16:39:47.000000Z",
            "updated_at": "2023-04-11T16:39:47.000000Z"
        },
        {
            "id": 5,
            "name": "American Samoa",
            "code": "AS",
            "capital": "Pago Pago",
            "currency": "United State Dollar",
            "currency_code": "USD",
            "currency_symbol": "$",
            "language": "English",
            "language_code": "en",
            "flag": "https://restcountries.eu/data/asm.svg",
            "mnos": null,
            "created_at": "2023-04-11T16:39:47.000000Z",
            "updated_at": "2023-04-11T16:39:47.000000Z"
        }
    ]
}
```
### 9. Specific Region
```
Header : must include appkey and bearer token
Method : Get
Link : /api/get_region/KE
```

**_Response_**
```json
{
    "status": true,
    "data": {
        "id": 118,
        "name": "Kenya",
        "code": "KE",
        "capital": "Nairobi",
        "currency": "Kenyan shilling",
        "currency_code": "KES",
        "currency_symbol": "Sh",
        "language": "English",
        "language_code": "en",
        "flag": "https://restcountries.eu/data/ken.svg",
        "mnos": null,
        "created_at": "2023-04-11T16:39:48.000000Z",
        "updated_at": "2023-04-11T16:39:48.000000Z"
    }
}
```
### 10. Products
```
Header : must include appkey and bearer token
Method : Get
Link : /api/get_products
```
**_Parameter_**
```json
{
    "start": 0,
    "end": 10,
    "type": "paywall",
    "site": "nation" //set to filter by site
}
```
**_Response_**
```json
{
  "status": true,
  "data": [
    {
      "id": 1,
      "identifier": "DN-KE",
      "product_name": "Daily Nation",
      "payment_methods": [
        "3"
      ],
      "product_link": "https://epaper.nation.africa/product/daily-nation",
      "user_id": 1,
      "payment_notification_link": null,
      "status": 1,
      "region_id": 118,
      "created_at": "2023-04-11T17:25:16.000000Z",
      "updated_at": "2023-04-30T20:41:03.000000Z",
      "region": {
        "id": 118,
        "name": "Kenya",
        "code": "KE",
        "capital": "Nairobi",
        "currency": "Kenyan shilling",
        "currency_code": "KES",
        "currency_symbol": "Sh",
        "language": "English",
        "language_code": "en",
        "flag": "https://restcountries.eu/data/ken.svg",
        "mnos": null,
        "created_at": "2023-04-11T16:39:48.000000Z",
        "updated_at": "2023-04-11T16:39:48.000000Z"
      }
    }
  ]
}
```
### 11. Rates
```
Header : must include appkey 
Method : Get
Link : /api/get_rates/DM-UG 
DM-UG is the product slug
```
**_Response_**
```json
{
    "status": true,
    "data": [
        {
            "id": 34,
            "product_id": 6,
            "rate_type_id": 1,
            "cost": "1500.00",
            "currency": "UGX",
            "reserve_currency": "USD",
            "reserve_currency_cost": "0.40",
            "region_id": 235,
            "status": 1,
            "description": null,
            "user_id": 1,
            "type": "individual",
            "organization_id": 0,
            "start_date": "2000-01-01 00:00:00",
            "end_date": null,
            "created_at": "2023-04-11T17:25:21.000000Z",
            "updated_at": "2023-04-11T17:25:21.000000Z",
            "product": {
                "id": 6,
                "identifier": "DM-UG",
                "product_name": "Daily Monitor",
                "payment_methods": [
                    "1",
                    "2"
                ]
            },
            "rate_type": {
                "id": 1,
                "name": "Archive Issue",
                "period": 1
            }
        },
        {
            "id": 35,
            "product_id": 6,
            "rate_type_id": 2,
            "cost": "1000.00",
            "currency": "UGX",
            "reserve_currency": "USD",
            "reserve_currency_cost": "0.27",
            "region_id": 235,
            "status": 1,
            "description": null,
            "user_id": 1,
            "type": "individual",
            "organization_id": 0,
            "start_date": "2000-01-01 00:00:00",
            "end_date": null,
            "created_at": "2023-04-11T17:25:21.000000Z",
            "updated_at": "2023-04-11T17:25:21.000000Z",
            "product": {
                "id": 6,
                "identifier": "DM-UG",
                "product_name": "Daily Monitor",
                "payment_methods": [
                    "1",
                    "2"
                ]
            },
            "rate_type": {
                "id": 2,
                "name": "Daily",
                "period": 1
            }
        },
        {
            "id": 36,
            "product_id": 6,
            "rate_type_id": 3,
            "cost": "7000.00",
            "currency": "UGX",
            "reserve_currency": "USD",
            "reserve_currency_cost": "1.87",
            "region_id": 235,
            "status": 1,
            "description": null,
            "user_id": 1,
            "type": "individual",
            "organization_id": 0,
            "start_date": "2000-01-01 00:00:00",
            "end_date": null,
            "created_at": "2023-04-11T17:25:21.000000Z",
            "updated_at": "2023-04-11T17:25:21.000000Z",
            "product": {
                "id": 6,
                "identifier": "DM-UG",
                "product_name": "Daily Monitor",
                "payment_methods": [
                    "1",
                    "2"
                ]
            },
            "rate_type": {
                "id": 3,
                "name": "Weekly",
                "period": 7
            }
        },
        {
            "id": 37,
            "product_id": 6,
            "rate_type_id": 4,
            "cost": "25000.00",
            "currency": "UGX",
            "reserve_currency": "USD",
            "reserve_currency_cost": "6.67",
            "region_id": 235,
            "status": 1,
            "description": null,
            "user_id": 1,
            "type": "individual",
            "organization_id": 0,
            "start_date": "2000-01-01 00:00:00",
            "end_date": null,
            "created_at": "2023-04-11T17:25:21.000000Z",
            "updated_at": "2023-04-11T17:25:21.000000Z",
            "product": {
                "id": 6,
                "identifier": "DM-UG",
                "product_name": "Daily Monitor",
                "payment_methods": [
                    "1",
                    "2"
                ]
            },
            "rate_type": {
                "id": 4,
                "name": "Monthly",
                "period": 31
            }
        },
        {
            "id": 38,
            "product_id": 6,
            "rate_type_id": 5,
            "cost": "70000.00",
            "currency": "UGX",
            "reserve_currency": "USD",
            "reserve_currency_cost": "18.69",
            "region_id": 235,
            "status": 1,
            "description": null,
            "user_id": 1,
            "type": "individual",
            "organization_id": 0,
            "start_date": "2000-01-01 00:00:00",
            "end_date": null,
            "created_at": "2023-04-11T17:25:21.000000Z",
            "updated_at": "2023-04-11T17:25:21.000000Z",
            "product": {
                "id": 6,
                "identifier": "DM-UG",
                "product_name": "Daily Monitor",
                "payment_methods": [
                    "1",
                    "2"
                ]
            },
            "rate_type": {
                "id": 5,
                "name": "Quarterly",
                "period": 91
            }
        },
        {
            "id": 39,
            "product_id": 6,
            "rate_type_id": 6,
            "cost": "125000.00",
            "currency": "UGX",
            "reserve_currency": "USD",
            "reserve_currency_cost": "33.37",
            "region_id": 235,
            "status": 1,
            "description": null,
            "user_id": 1,
            "type": "individual",
            "organization_id": 0,
            "start_date": "2000-01-01 00:00:00",
            "end_date": null,
            "created_at": "2023-04-11T17:25:21.000000Z",
            "updated_at": "2023-04-11T17:25:21.000000Z",
            "product": {
                "id": 6,
                "identifier": "DM-UG",
                "product_name": "Daily Monitor",
                "payment_methods": [
                    "1",
                    "2"
                ]
            },
            "rate_type": {
                "id": 6,
                "name": "Half Yearly",
                "period": 183
            }
        },
        {
            "id": 40,
            "product_id": 6,
            "rate_type_id": 7,
            "cost": "240000.00",
            "currency": "UGX",
            "reserve_currency": "USD",
            "reserve_currency_cost": "64.07",
            "region_id": 235,
            "status": 1,
            "description": null,
            "user_id": 1,
            "type": "individual",
            "organization_id": 0,
            "start_date": "2000-01-01 00:00:00",
            "end_date": null,
            "created_at": "2023-04-11T17:25:21.000000Z",
            "updated_at": "2023-04-11T17:25:21.000000Z",
            "product": {
                "id": 6,
                "identifier": "DM-UG",
                "product_name": "Daily Monitor",
                "payment_methods": [
                    "1",
                    "2"
                ]
            },
            "rate_type": {
                "id": 7,
                "name": "Annually",
                "period": 365
            }
        }
    ]
}
```
### 12. Save Leads
```
Header : must include appkey and bearer token
Method : Post
Link : /api/leads 
```
**_Parameters_**
```json
{
    "amount": 30,
    "link":"https://nation.africa/resource/image/4186638/landscape_ratio3x2/900/600/cf409bb2e693874d0f5cfa4004edb924/dN/june-jerop.jpg",
    "product_id":1,
    "rate_id": 2
}
```
**_Response_**
```json
{
    "status": true,
    "data": "Saved successfully"
}
```
### 13. Add to Cart
```
Header : must include appkey and bearer token
Method : Post
Link : /api/cart
```
**_Parameters_**
```json
{
    "rate_id": 3,
    "thumbnail":"https://nation.africa/resource/image/4186638/landscape_ratio3x2/900/600/cf409bb2e693874d0f5cfa4004edb924/dN/june-jerop.jpg"
}
```
**_Response_**
```json
{
    "status": true,
    "data": {
        "message": "Cart added successfully"
    }
}
```
### 13. Remove from Cart
```
Header : must include appkey and bearer token
Method : Patch
Link : /api/remove_from_cart
```
**_Parameters_**
```json
{
    "cart_item_id":18
}
```
**_Response_**
```json
{
    "status": true,
    "data": {
        "message": "Cart item removed successfully"
    }
}
```
### 14. Get Cart Details
```
Header : must include appkey and bearer token
Method : Get
Link : /api/get_cart
```
**_Response_**
```json
{
    "status": true,
    "data": {
        "id": 8,
        "user_id": 1,
        "organization_id": 1,
        "status": 0,
        "created_at": "2023-05-07T12:46:09.000000Z",
        "updated_at": "2023-05-07T12:46:09.000000Z",
        "amount": "2.05",
        "currency": "USD",
        "items": [
            {
                "id": 18,
                "cart_id": 8,
                "rate_id": 3,
                "product": "Daily Nation",
                "cost": "2.05",
                "currency": "USD",
                "thumbnail": "https://nation.africa/resource/image/4186638/landscape_ratio3x2/900/600/cf409bb2e693874d0f5cfa4004edb924/dN/june-jerop.jpg",
                "rate_type": "Weekly",
                "created_at": "2023-05-07T12:46:11.000000Z",
                "updated_at": "2023-05-07T12:46:11.000000Z"
            }
        ]
    }
}
```
### 15. Check Coupons
```
Header : must include appkey and bearer token
Method : Post
Link : /api/check_coupon
```
**_Parameters_**
```json
{
    "coupon_code":"Xveeddd"
}
```
**_Response_**
<br>
*__On Fail__*
```json
{
    "status": true,
    "data": null
}
```
*__On Success__*

