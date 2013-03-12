; <?php /*

[name]

not empty = 1

[email]

email = 1
unique = "#prefix#user.email"

[password]

length = "6+"

[verify_pass]

matches = "$_POST['password']"

[org_name]

not empty = 1

[subdomain]

not empty = 1
not equals = "www"
regex = "/^[a-z0-9-]+$/"
unique = "#prefix#saasy_org.subdomain"

; */ ?>