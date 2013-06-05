1. Demonstrate connecting to mysql

Expect $db

2. Given $year = date("Y")

Given $discounted_price = '$19.96'

Given $query = "INSERT INTO `discounts` (`resource_id`, `type`, `value`, `description`, `starts_at`, `ends_at`) VALUES (347, 'product', 20, 'Get 20% off any product in the Social Skills section', '" . $year . "-01-01 00:00:00', '" . $year . "-12-31 23:59:59')"

Demonstrate executing a query

Given $url = "http://localhost/Social-Skills/Any-Game-Cards/TCT-8"

Demonstrate GETTING

Expect false !== strstr($body, $discounted_price)

Expect false !== strstr($body, 'Get 20% off any product in the Social Skills section')

Given $query = "DELETE FROM discounts WHERE id = '" . mysql_insert_id() . "'"

Demonstrate executing a query

Demonstrate GETTING

Expect false === strstr($body, $discounted_price)

Expect false === strstr($body, 'Get 20% off any product in the Social Skills section')