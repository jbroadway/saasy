; <?php /*

[name]

not empty = 1

[email]

email = 1
callback = "saasy\Validator::email"

[new_pass]

skip_if_empty = 1
length = "6+"

[ver_pass]

matches = "$_POST['new_pass']"

[photo]

skip_if_empty = 1
file = 1
filetype = "jpg, png, gif"

; */ ?>