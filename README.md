# PwsAuth

PwsAuth is an authentication protocol throught http header designed to web services

## Request Headers

request headers must be define as follow  :

    Pws-Authorization : $type $token
    Pws-Ident : $userkey

the **$token** can be either a `loginToken` or a `sessionToken`

the **$token** is divided in four part

* a datetime formatted with the `Authenticator::DATE_FORMAT` format
* an obfuscate part 's token builded by date, common salt & the third token 's part
* a loginToken representing a user signed token for a specific login at given date
  OR 
  a session token representing the session id
* noise data to be removed

the complete token is valid only if obfuscate part can be rebuild  
this simple mecanism ensure that **sessionId** is valid and can be safety load

Authenticator 's configuration comes with a `hash.session.index` and `hash.noise.length` values 
wich can be redefined to move the session token part into the complete token

                                                        << hash.session.index >>                             << hash.noise.length >>
    |-----------------------------------------------------------<<-^->>---------------------------------------------<<-^->>--------|
    |- type -|-- date ---|------------ obfuscate token ---------<<-^->>-------------- session token ----------------<<-^->> noise -|
    |        |     1     |                    2                    |                         3                         |     4     |
     PwsAuth2 242003031711e1a6104135f04c6c01e6cd5952ecafbb53c928603b0gb64tqo609qse6ovd7lhdvk4fnaqk7cdl26e4d4qh7jb41eu5f1zb5y79m8pgu3


### ClientSide

a request header can be generated via the `generateHeader($login, $key, $sessid=null)` method
the third parameter determine wich kind of token will be generated

### ServerSide

the Token can be retriew via the `getToken` method

`loginToken` is validate by the `check(Token $token, $login)` method
`loginToken` must match a public url with method `POST` and a couple of login/password
on successfull login, the session id must be transmit to the client.

`sessionToken` is valid only if the session can effectively be loaded, and the 
user key match the given `Pws-Ident` value

### Configuration

configuration must be the same on server and client sides  
hash definition is a convenient way to obfuscate your tokens

```yaml
pwsauth :

    type    : PwsAuth2

    header  :
        auth            : Pws-Authorization
        ident           : Pws-Ident

    salt    : 
        common          : jK5#p9Mh5.Zv}
        # used for generating user specific salt
        user.index      : 10
        user.length     : 12
    
    hash    :
        sep             : /
        algo            : sha256
        # effective token length size. out of bound data is simply noise
        length          : 52
        # session index (or obfuscate length)
        session.index   : 58
        # ending noise data length)
        noise.length    : 12
```

### Authenticator instanciation

```php
<?php
require_once(__dir__ . '/vendor/autoload.php');

use Symfony\Component\Yaml\Yaml;
use MetaTech\PwsAuth\Authenticator;

$config        = Yaml::parse(file_get_contents(__dir__ . '/config/pwsauth.yml'));
$authenticator = new Authenticator($config['pwsauth']);
```

### Notes

a valid `$userkey` alone is useless  
a valid `$sessionId` alone is useless