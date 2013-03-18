; <?php

[App Settings]

; The name of your SAAS app.

app_name = My SAAS App

; The URL to map to your app (e.g., 'myapp' if you want
; your app accessible at the URL /myapp).

app_alias = myapp

; A custom handler that generates your custom footer
; navigation links.

footer = Off

; A custom handler that loads your bootstrap theme
; customizations.

theme = Off

; A custom handler that provides search capabilities
; for your app.

search = Off

; A static method to call for a list of access limits
; for each account level.

limits = Off

[Sections]

; A list of sections for the app. These take the form:
;
; urlcomponent[yourapp/handlername] = Section Name
;
; For example:
;
; clients[myapp/clients] = Clients
;
; Dropdown menus can also be added like this:
;
; dropdown:options[] = Options
; dropdown:options[myapp/option1] = Option A
; dropdown:options[myapp/option2] = Option B

[Emails]

; The welcome email for new organizations

welcome = saasy/email/welcome

; The welcome email for new accounts within an organization

new_account = saasy/email/new_account

[Admin]

handler = saasy/admin
name = SaaS Manager
install = saasy/install
upgrade = saasy/upgrade
version = 0.9.0-beta

; */ ?>