# create subscription

-this part is  either wrongly documented or not well implemented, as subscription needs to be created on creating an invoice with product type subscription and not just by passing customer_id and product_id without knowing exactly which price plan do the customer subscribed

once subscription is successfully created, the response should display invoice details, amount to pay, control numbers, payment links , subscription start and end date etc and not this response indicated

/api/v1/subscriptions
Create Subscription
▼
Create a new subscription for a customer

Request Body
{
  "customer_id": 5,
  "product_id": 2,
  "start_date": "2024-03-15"
}

# 