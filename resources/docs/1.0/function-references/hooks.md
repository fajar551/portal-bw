# Hooks

---

- [Namespace](#namespace)
- [Functions](#functions)

<a name="namespace"></a>
## Namespace

```php
use App\Hooks;
```

---
<a name="functions"></a>
## Functions

```php
public static function run_hook($hook_name, array $args = [], $unpackArguments = false)
```
Fungsi ini untuk memanggil [Events](/{{route}}/{{version}}/hooks/events) dengan parameter yang disediakan berupa array.
<br>
<br>
Cara pemanggilan:

```php
Hooks::run_hook("TicketClose", array("ticketid" => $id));
```