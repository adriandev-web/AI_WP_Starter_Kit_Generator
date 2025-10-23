=== Ballsquad External Auth ===
Contributors: ballsquad
Tags: authentication, external auth, ballsquad, woocommerce
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Integracja zewnętrznego systemu uwierzytelniania Ballsquad z WordPress/WooCommerce.

== Description ==

Plugin "Ballsquad External Auth" umożliwia integrację WordPress/WooCommerce z zewnętrznym systemem uwierzytelniania Ballsquad. Plugin implementuje nowoczesne podejście "single source of truth" - dane użytkowników są pobierane z API Ballsquad w czasie rzeczywistym, bez duplikowania w WordPress.

= Główne funkcje =

* **Uwierzytelnianie przez API Ballsquad** - użytkownicy logują się używając danych z systemu Ballsquad
* **JWT Token Authentication** - bezpieczne przechowywanie tokenów w cookies
* **Minimalne przechowywanie danych** - tylko external_id i podstawowe dane w WordPress
* **Cache'owanie z automatycznym odświeżaniem** - optymalizacja wydajności
* **Mapowanie ról** - automatyczne przypisywanie ról WordPress na podstawie ról z API
* **Blokowanie lokalnej rejestracji** - opcjonalne wymuszenie rejestracji przez system Ballsquad
* **Kompatybilność z WooCommerce** - pełna integracja z e-commerce
* **REST API Proxy** - możliwość komunikacji z API Ballsquad po zalogowaniu
* **Wyświetlanie danych na frontendzie** - shortcode'y, widgety i hooki do wyświetlania danych użytkownika

= Wymagania =

* WordPress 5.0 lub nowszy
* PHP 7.4 lub nowszy
* WooCommerce 5.0 lub nowszy (opcjonalnie)
* Dostęp do API Ballsquad

== Installation ==

1. Skopiuj folder `ballsquad-external-auth` do katalogu `/wp-content/plugins/`
2. Aktywuj plugin w panelu administracyjnym WordPress
3. Przejdź do Ustawienia > Ballsquad Auth
4. Skonfiguruj ustawienia API i mapowanie ról

== Configuration ==

= Podstawowa konfiguracja =

1. **API Base URL**: Ustaw URL do API Ballsquad
   * Production: `https://api.ballsquad.pl/api` ✅ **Działa**
   * Staging: `https://api-stage.ballsquad.pl/api` ❌ **Wymaga innych danych**

2. **Use Staging**: Zaznacz jeśli chcesz używać środowiska staging

3. **Block Local Registration**: Zaznacz aby zablokować lokalną rejestrację użytkowników

4. **Roles Map JSON**: Skonfiguruj mapowanie ról z systemu Ballsquad na role WordPress
   ```json
   {
     "ROLE_USER": "customer",
     "ROLE_ADMIN": "administrator"
   }
   ```
   
   **Uwaga**: API zwraca role w polu `authorities` jako `["ROLE_USER"]`
   Przykład: `{"CUSTOMER":"customer","ADMIN":"administrator","MODERATOR":"shop_manager"}`

5. **Synchronizacja danych**: Skonfiguruj automatyczną synchronizację danych użytkowników
   * **Enable Webhook Sync**: Włącz synchronizację przez webhook
   * **Webhook Secret**: Sekret do weryfikacji webhook (opcjonalny)
   * **Enable Cron Sync**: Włącz okresową synchronizację przez cron
   * **Cron Interval**: Interwał synchronizacji (hourly, twicedaily, daily)

= Testowanie =

1. Przejdź do strony "Moje konto" WooCommerce
2. Spróbuj zalogować się używając danych z systemu Ballsquad
3. Sprawdź czy użytkownik został utworzony w WordPress
4. Sprawdź czy cookie `ext_jwt` zostało ustawione
5. Wyloguj się i sprawdź czy request do `/account/logout` został wysłany

= Jak działa plugin =

Plugin implementuje nowoczesne podejście "single source of truth" zgodnie z najlepszymi praktykami:

## 🎯 **Uwierzytelnianie i autoryzacja**

1. **Logowanie** - użytkownik podaje dane z systemu Ballsquad
2. **Uwierzytelnianie** - plugin wysyła dane do API Ballsquad
3. **Otrzymanie tokena** - plugin otrzymuje JWT token z API
4. **Autoryzacja** - token jest używany do wszystkich kolejnych requestów

## 📊 **Zarządzanie danymi użytkownika**

1. **Minimalne przechowywanie** - plugin przechowuje tylko external_id w WordPress
2. **Pobieranie z API** - wszystkie dane użytkownika są pobierane z API w czasie rzeczywistym
3. **Cache'owanie** - dane są cache'owane na 15 minut dla optymalizacji wydajności
4. **Automatyczne odświeżanie** - cache jest automatycznie odświeżany przy wygaśnięciu

## 🔄 **Komunikacja z API**

1. **REST API Proxy** - plugin udostępnia metody do komunikacji z API Ballsquad
2. **Bearer Token Authentication** - wszystkie requesty używają JWT tokena
3. **Automatyczne zarządzanie tokenem** - sprawdzanie ważności i odświeżanie
4. **Obsługa błędów** - automatyczne przekierowanie do logowania przy wygaśnięciu tokena

## 🚫 **Czego plugin NIE robi (domyślnie)**

- **Nie duplikuje danych** - nie przechowuje pełnych danych użytkownika w WordPress
- **Nie synchronizuje dwukierunkowo** - nie wysyła zmian z WordPress do API (opcjonalne)
- **Nie przechowuje haseł** - wszystkie operacje uwierzytelniania przez API
- **Nie wymaga webhooków** - dane są pobierane na żądanie

## ⚙️ **Opcje konfiguracji**

### **Wyłączenie edycji profilu (Zalecane)**
Plugin domyślnie wyłącza edycję danych użytkownika w WordPress dla użytkowników zewnętrznych, wyświetlając komunikat informacyjny o konieczności edycji w aplikacji Ballsquad.

### **Synchronizacja dwukierunkowa (Opcjonalna)**
Jeśli potrzebujesz synchronizacji dwukierunkowej, możesz ją włączyć w ustawieniach pluginu:
1. Przejdź do **Ustawienia > Ballsquad Auth**
2. W sekcji **Data Synchronization** zaznacz **Enable Bidirectional Sync**
3. **Uwaga**: Ta opcja może prowadzić do problemów z niespójnością danych

= Używanie danych użytkownika =

Plugin udostępnia prosty sposób na pobieranie danych użytkownika z API:

## 📝 **Przykłady użycia**

```php
// Pobierz instancję pluginu
$plugin = BallsquadExternalAuth::get_instance();
$user_data_provider = $plugin->get_user_data_provider();

// Pobierz dane użytkownika (z cache)
$user_data = $user_data_provider->get_user_data($user_id);

// Pobierz konkretne dane
$first_name = $user_data_provider->get_user_first_name($user_id);
$last_name = $user_data_provider->get_user_last_name($user_id);
$email = $user_data_provider->get_user_email($user_id);
$phone = $user_data_provider->get_user_phone($user_id);

// Pobierz świeże dane z API (bez cache)
$fresh_data = $user_data_provider->get_fresh_user_data();
```

## 🎨 **Wyświetlanie danych na frontendzie**

Plugin udostępnia kilka sposobów na wyświetlanie danych użytkownika na frontendzie:

### **Shortcode'y**

```php
// Podstawowe dane użytkownika
[ballsquad_user_data fields="name,email,phone" show_title="true"]

// Pełny profil użytkownika
[ballsquad_user_profile show_avatar="true" show_stats="true"]

// Statystyki użytkownika
[ballsquad_user_stats show_physical="true" show_skills="true"]
```

### **Widget**

Plugin dodaje widget "Ballsquad User Data" który można umieścić w sidebarach i innych obszarach widgetów.

### **Dostępne pola**

**Podstawowe dane:**
- `name` - Imię i nazwisko
- `firstname` - Imię
- `lastname` - Nazwisko
- `email` - Email
- `phone` - Telefon
- `city` - Miasto
- `zip` - Kod pocztowy
- `birth` / `birthdate` - Data urodzenia

**Dane fizyczne:**
- `height` - Wzrost
- `weight` - Waga

**Umiejętności sportowe:**
Plugin pobiera umiejętności sportowe z endpointu `/user-sport-skills/user` i wyświetla je z poziomami zaawansowania zgodnie z aplikacją Ballsquad:
- Poziom 1: Początkujący
- Poziom 2: Średni
- Poziom 3: Średnio zaawansowany
- Poziom 4: Zaawansowany
- Poziom 5: Ekspert

Przykład: "Rugby: Średni", "Piłka nożna: Zaawansowany", "Siatkówka plażowa: Średnio zaawansowany"

## 🔄 **Cache'owanie**

- **Czas cache'owania**: 15 minut (konfigurowalny)
- **Automatyczne odświeżanie**: przy wygaśnięciu cache
- **Czyszczenie cache**: przy wylogowaniu użytkownika
- **Ręczne czyszczenie**: `$user_data_provider->clear_user_cache($user_id)`

= Test połączenia API =

**Uwaga**: Test połączenia może zwrócić błąd HTTP 401 (Unauthorized), co jest normalne - oznacza to, że serwer API jest dostępny, ale wymaga uwierzytelnienia dla pełnego dostępu. To nie jest błąd konfiguracji pluginu.

**Interpretacja wyników testu:**
- ✅ **HTTP 200-299**: Serwer odpowiada poprawnie
- ⚠️ **HTTP 401**: Serwer dostępny, wymaga uwierzytelnienia (normalne)
- ⚠️ **HTTP 403**: Serwer dostępny, dostęp ograniczony
- ⚠️ **HTTP 404**: Serwer dostępny, endpoint nie istnieje
- ❌ **HTTP 500+**: Błąd serwera - sprawdź konfigurację API
- ❌ **Timeout/Connection failed**: Problem z połączeniem sieciowym

= Debugowanie problemów z logowaniem =

Jeśli logowanie przez system Ballsquad nie działa, sprawdź logi WordPress:

1. **Włącz debugowanie** w `wp-config.php`:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```

2. **Sprawdź logi** w `/wp-content/debug.log`

3. **Szukaj wpisów** zaczynających się od `[BSEA]`:
   - `[BSEA] Authentication attempt` - próba logowania
   - `[BSEA] Making POST request to` - request do API
   - `[BSEA] API Response` - odpowiedź z API
   - `[BSEA] HTTP 401/400/500` - błędy API

4. **Typowe problemy**:
   - **Brak wpisów [BSEA]**: Plugin nie jest aktywny lub nie jest załadowany
   - **HTTP 401**: Nieprawidłowe dane logowania lub problem z API
   - **HTTP 500**: Błąd serwera API
   - **Network error**: Problem z połączeniem sieciowym

= Status API =

**Production API** (`https://api.ballsquad.pl/api`):
* ✅ `/authenticate` - działa
* ✅ `/account/logout` - działa  
* ✅ `/account` - pobiera pełne dane użytkownika (imię, nazwisko)

**Staging API** (`https://api-stage.ballsquad.pl/api`):
* ❌ `/authenticate` - zwraca "Bad credentials"
* ❓ `/account/logout` - nie przetestowano
* ❓ `/account/me` - nie przetestowano

**Uwaga**: Plugin automatycznie pobiera dane użytkownika z JWT tokena, gdy endpoint `/account/me` nie jest dostępny.

== Frequently Asked Questions ==

= Czy plugin jest bezpieczny? =

Tak, plugin nie przechowuje haseł użytkowników w WordPress. Wszystkie operacje uwierzytelniania są wykonywane przez zewnętrzne API Ballsquad.

= Czy mogę używać plugin bez WooCommerce? =

Tak, plugin działa również z standardowym WordPress, ale niektóre funkcje (jak blokowanie rejestracji) mogą wymagać dodatkowej konfiguracji.

= Jak zmienić mapowanie ról? =

Przejdź do Ustawienia > Ballsquad Auth i edytuj pole "Roles Map JSON". Format to JSON gdzie klucze to role z systemu Ballsquad, a wartości to role WordPress.

= Co jeśli API zwraca błąd? =

Plugin loguje błędy do logów WordPress (jeśli WP_DEBUG jest włączone). Sprawdź logi aby zdiagnozować problem.

== Screenshots ==

1. Panel ustawień pluginu
2. Formularz logowania z integracją
3. Mapa ról użytkowników

== Changelog ==

= 1.7.0 =
* **NOWA FUNKCJONALNOŚĆ**: Wyświetlanie danych użytkownika na frontendzie
* **Dodano shortcode'y**: `[ballsquad_user_data]`, `[ballsquad_user_profile]`, `[ballsquad_user_stats]`
* **Dodano widget**: "Ballsquad User Data" do wyświetlania danych w sidebarach
* **Dodano style CSS**: Automatyczne style dla wyświetlania danych na frontendzie
* **Dodano AJAX**: Przycisk odświeżania danych użytkownika
* **Dodano hooki**: Automatyczne dodawanie stylów i skryptów na frontend
* **Rozszerzono funkcjonalność**: Możliwość wyświetlania wszystkich danych z API Ballsquad
* **Poprawiono UX**: Responsywne style i intuicyjny interfejs

= 1.6.1 =
* **Czyszczenie kodu** - usunięto wszystkie pliki testowe i pomocnicze
* **Optymalizacja rozmiaru** - wtyczka jest teraz lżejsza i bardziej profesjonalna
* **Usunięte pliki**: test-api.php, test-hooks.php, test-initialization.php, fix-menu-duplicates.php i inne pliki testowe
* **Zachowana funkcjonalność** - wszystkie funkcje wtyczki działają bez zmian

= 1.6.0 =
* **PRZEŁOMOWA ZMIANA**: Implementacja podejścia "single source of truth"
* **Usunięto duplikowanie danych** - plugin przechowuje tylko external_id w WordPress
* **Dodano cache'owanie API** - dane są pobierane z API i cache'owane na 15 minut
* **Uproszczono architekturę** - usunięto niepotrzebne komponenty synchronizacji
* **Dodano User_Data_Provider** - prosty sposób na pobieranie danych użytkownika
* **Usunięto synchronizację dwukierunkową** - dane są pobierane tylko z API
* **Zoptymalizowano wydajność** - minimalne przechowywanie danych w WordPress
* **Zaktualizowano dokumentację** - zgodnie z nowym podejściem

= 1.5.4 =
* Dodano statyczne flagi do wszystkich klas komponentów - hooki są rejestrowane tylko raz
* Naprawiono problem z wielokrotnym rejestrowaniem hooków WordPress
* Dodano sprawdzanie czy komponenty już istnieją przed inicjalizacją
* Poprawiono stabilność - unikamy duplikowania hooków i komponentów
* Zoptymalizowano wydajność - eliminacja niepotrzebnych inicjalizacji

= 1.5.3 =
* Naprawiono problem z wielokrotną inicjalizacją komponentów pluginu - zaimplementowano Dependency Injection
* Zoptymalizowano wydajność - komponenty są inicjalizowane tylko raz
* Dodano gettery do głównej klasy pluginu dla lepszego dostępu do komponentów
* Poprawiono stabilność - unikamy tworzenia wielokrotnych instancji klas
* Dodano sprawdzanie stanu inicjalizacji pluginu przed użyciem komponentów

= 1.5.2 =
* Naprawiono problem z wielokrotnymi komunikatami sukcesu - teraz wyświetla się tylko raz
* Naprawiono problem z powielonymi sekcjami "Ballsquad Profile Data" w panelu administracyjnym
* Naprawiono problem z external_id - teraz jest poprawnie zapisywany
* Dodano sprawdzanie czy hooki już zostały zarejestrowane
* Poprawiono stabilność i wydajność pluginu

= 1.5.1 =
* Naprawiono błąd PHP Fatal error w sekcji "Ballsquad Profile Data" w panelu administracyjnym
* Poprawiono obsługę pola sportTypes - teraz bezpiecznie sprawdza czy jest tablicą
* Dodano lepsze sprawdzanie danych profilu przed wyświetleniem
* Poprawiono stabilność panelu administracyjnego

= 1.5.0 =
* Naprawiono błąd walidacji przy aktualizacji danych - teraz wysyła wszystkie wymagane pola
* Dodano obsługę wszystkich pól wymaganych przez API (attack, defense, jump, speed, itp.)
* Poprawiono logikę synchronizacji - używa /account do pobierania, /user-profiles do aktualizacji
* Dodano domyślne wartości dla wymaganych pól API
* Poprawiono komunikaty błędów i logowanie diagnostyczne

= 1.4.0 =
* Dodano pobieranie pełnych danych profilu użytkownika z API Ballsquad
* Dodano sekcję "Ballsquad Profile Data" w panelu administracyjnym WordPress
* Zapisywanie wszystkich danych profilu (attack, defense, birthDate, phoneNumber, itp.)
* Wyświetlanie danych fizycznych, ustawień i preferencji użytkownika
* Poprawiono synchronizację z pełnym profilem użytkownika

= 1.3.0 =
* Naprawiono endpoint do aktualizacji danych użytkownika - teraz używa PUT /api/user-profiles
* Dodano pobieranie aktualnych danych z API przed aktualizacją
* Poprawiono synchronizację dwukierunkową - zmiany w WordPress są teraz poprawnie wysyłane do API
* Dodano obsługę pełnego profilu użytkownika w API
* Naprawiono błąd HTTP 405 (Method Not Allowed) przy aktualizacji danych
* Dodano szczegółowe logowanie procesu synchronizacji danych

= 1.2.0 =
* Dodano JWT Token Authentication - automatyczne pobieranie aktualnych danych użytkownika
* Dodano dwukierunkową synchronizację - zmiany w WordPress są wysyłane do API Ballsquad
* Dodano obsługę zmiany hasła przez API Ballsquad
* Dodano cache danych użytkownika z automatycznym odświeżaniem
* Dodano integrację z formularzami WooCommerce
* Dodano obsługę metod płatności z API Ballsquad
* Poprawiono wydajność - dane są pobierane tylko gdy potrzebne
* Dodano automatyczne odświeżanie danych przy każdym logowaniu

= 1.1.0 =
* Dodano automatyczną synchronizację danych użytkowników z aplikacji Ballsquad
* Dodano webhook endpoint do odbierania powiadomień o zmianach danych
* Dodano cron job do okresowej synchronizacji danych
* Dodano REST API do ręcznej synchronizacji użytkowników
* Dodano panel administracyjny do zarządzania synchronizacją
* Dodano statystyki synchronizacji w panelu administracyjnym
* Rozszerzono REST Proxy o metody do pobierania i aktualizacji danych użytkowników
* Dodano obsługę różnych typów zdarzeń webhook (user_created, user_updated, user_deleted)

= 1.0.9 =
* Naprawiono problem z zapisywaniem imienia i nazwiska użytkownika
* Dodano obsługę kluczy API Ballsquad (firstName, lastName)
* Dodano automatyczne tworzenie display_name z firstName + lastName
* Dodano szczegółowe logowanie procesu aktualizacji danych użytkownika
* Poprawiono logikę wyciągania danych z profilu użytkownika

= 1.0.8 =
* Dodano obsługę endpointu /api/account do pobierania pełnych danych użytkownika
* Plugin automatycznie pobiera imię i nazwisko z API Ballsquad po uwierzytelnieniu
* Dodano szczegółowe logowanie procesu pobierania profilu użytkownika
* Poprawiono obsługę błędów przy pobieraniu danych użytkownika
* Dodano fallback do JWT payload jeśli API nie odpowiada

= 1.0.7 =
* Naprawiono problem z tworzeniem użytkowników - email jest teraz pobierany z username
* Dodano logowanie JWT payload dla debugowania
* Poprawiono logikę wyciągania danych użytkownika z JWT tokena
* Dodano szczegółowe logowanie procesu tworzenia użytkowników
* Naprawiono problem z brakiem email w danych użytkownika

= 1.0.6 =
* Dodano szczegółowe logowanie diagnostyczne dla integracji z logowaniem WordPress
* Poprawiono debug info dla prób uwierzytelnienia
* Dodano logowanie wszystkich requestów do API
* Dodano logowanie odpowiedzi API z kodami statusu
* Dodano sprawdzenie inicjalizacji komponentów pluginu
* Ułatwiono debugowanie problemów z logowaniem

= 1.0.5 =
* Naprawiono obsługę błędu HTTP 401 w teście połączenia
* Zmieniono test połączenia na HEAD request zamiast GET
* Dodano lepszą interpretację kodów odpowiedzi HTTP
* Błąd 401 jest teraz traktowany jako sukces (serwer dostępny)
* Dodano dokumentację interpretacji wyników testu połączenia
* Poprawiono komunikaty błędów dla różnych kodów HTTP

= 1.0.4 =
* Naprawiono problem z zawieszającym się testem połączenia
* Dodano timeout 30 sekund dla AJAX requestów
* Dodano szczegółowe logowanie błędów w konsoli przeglądarki
* Dodano przycisk "Reset" do resetowania stanu testu połączenia
* Poprawiono obsługę błędów AJAX z lepszymi komunikatami
* Dodano sprawdzenie dostępności komponentów przed wykonaniem testu

= 1.0.3 =
* Naprawiono błąd "Network error occurred" w teście połączenia API
* Dodano obsługę AJAX dla testu połączenia
* Poprawiono obsługę błędów sieciowych w Auth Client
* Dodano szczegółowe logowanie diagnostyczne dla testów połączenia
* Poprawiono komunikaty błędów dla różnych typów problemów sieciowych

= 1.0.2 =
* Naprawiono problem z wielokrotnym dodawaniem menu w panelu administracyjnym
* Dodano sprawdzenie inicjalizacji pluginu
* Poprawiono Singleton pattern

= 1.0.1 =
* Naprawiono obsługę danych użytkownika z JWT tokena
* Zaktualizowano mapowanie ról dla `ROLE_USER` i `ROLE_ADMIN`
* Dodano obsługę pola `authorities` z JWT
* Poprawiono obsługę braku endpointu `/account/me`
* Dodano status API w dokumentacji

= 1.0.0 =
* Pierwsza wersja pluginu
* Integracja z API Ballsquad
* Automatyczne tworzenie użytkowników
* Mapowanie ról
* Obsługa JWT tokenów

== Upgrade Notice ==

= 1.0.0 =
Pierwsza wersja pluginu - zalecana dla wszystkich użytkowników.

== Support ==

W przypadku problemów lub pytań, skontaktuj się z zespołem Ballsquad.

== TODO ==

* [ ] Obsługa refresh tokenów
* [ ] Dodatkowe opcje mapowania ról
* [ ] Integracja z dodatkowymi endpointami API
* [ ] Rozszerzone logowanie diagnostyczne
* [ ] Wsparcie dla dodatkowych typów tokenów
* [ ] Obsługa synchronizacji danych adresowych
* [ ] Integracja z WooCommerce customer data
* [ ] Dashboard widget z statystykami synchronizacji
