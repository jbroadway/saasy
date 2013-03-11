; <?php

[App Settings]

; The name of your SAAS app.

app_name = My SAAS App

; The URL to map to your app (e.g., 'myapp' if you want
; your app accessible at the URL /myapp).

app_url = myapp

; A custom handler that generates your custom footer
; navigation links.

footer = Off

; A custom handler that loads your bootstrap theme
; customizations.

theme = Off

; A custom handler that provides search capabilities
; for your app. Your handler should accept a boolean
; 'header' parameter that tells it to show only the
; search form, for embedding in the header of the site.

search = Off

[Sections]

; A list of sections for the app. These take the form:
;
; urlcomponent[yourapp/handlername] = Section Name
;
; For example:
;
; clients[myapp/clients] = Clients

[Admin]

handler = saasy/admin
name = SAASy
install = saasy/install
upgrade = saasy/upgrade
version = 0.9.0-beta

; */ ?>