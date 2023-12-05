# Ataków Infiltracji Plikach

## Informations

### Wprowadzenie

Witajcie w laboratorium testowania ataków infiltracji plików! Cieszymy się, że możemy was gościć w tym środowisku, gdzie będziemy eksplorować różne scenariusze ataków w celu zrozumienia i wzmocnienia poziomu bezpieczeństwa systemów informatycznych..

### Cel Laboratorium

Celem tego laboratorium jest zapewnienie uczestnikom praktycznego doświadczenia w zakresie identyfikacji, analizy i przeciwdziałania różnym rodzajom ataków na systemy informatyczne. Będziemy skupiać się na rzeczywistych przypadkach ataków, aby uczestnicy mogli zdobyć praktyczne umiejętności w zakresie bezpieczeństwa informatycznego.

### Rozpoczęcie laboratorium

1. Przejdź do katalogu projektu

```
docker-compose up --build
```

### Przywrócenia laboratorium do stanu początkowego

W razie gdyby pojawiły się problemy związane z środowiskiem laboratorium lub po dobrze przeprowadzonym ataku proszę:

1. Przejdź do katalogu projektu

```
docker rm ma0-tdi-mariadb
docker rm ma0-tdi-phpmyadmin
docker rm ma0-tdi-php-apache

# lub

docker-compose.exe rm

# następnie odpal projekt:

docker-compose up --build
```

### Zatrzymanie docker-compose:

Wystaczy kliknąć _Ctrl+C_

#### Serwisy hostowane przez docker-compose:

- w celu przeprowadzaniea laboratorium przejdź na stronę [WebServer](http://localhost:7000/)
- do przeprowadzania ataków korzystaj tylko strony internetowej. Zaloguj się do niej za pomocą: Login: "user1" i Hasło "user1_password"
- Zakładamy, że jako zywczajny użytkownik nie masz dostęp do bazy, ale jeśli chciałbyś potestować funkcjnalność serwera możesz to zrobić logująć się [TU!](http://localhost:7002/): Login: "tdi" i Hasło "tdi"

| Name       | URL       | Port |
| ---------- | --------- | ---- |
| Database   | localhost | 7001 |
| PHP        | localhost | 7000 |
| PHPMyAdmin | localhost | 7002 |

![Strona początkowa pokazana po zalogowaniu](/img/intro.png)

### Laboratorium

W tym laboratorium będziesz miał okazję przeprowadzać ataki na serwer Apache, który hostuje prostą stronę internetową napisaną w języku PHP. Strona umożliwia żądanie dostępu do pomieszczeń i budynków. Jednak, aby uzyskać dostęp, musisz:

- przesłać dwa certyfikaty ukończenia kursów (Upload atatck)
- wysłac zgloszenie (XSS)
- uzyskać zgodę swojego managera oraz administratora budynków ()

## Zad 1

### Analiza projektu.

1. Otwórz stronę [WebServer](http://localhost:7000/)
2. Zaloguj się, używając konta: _user1:user1_password_. Zauważ, że monit logowania pojawia się przed wyrenderowaniem strony. To uwierzytelnianie jest zaimplementowane po stronie serwera Apache, a nie za pomocą naszego kodu PHP. Przeczytaj więcej o module [mod_auth](https://httpd.apache.org/docs/2.4/howto/auth.html). Jakie są wady i zalety tego rozwiązania?

   - Jedną z głównych wad jest to, że nie można się wylogować, a jedynym sposobem jest odczekanie, aż sesja wygaśnie (około 15 minut).
   - Zaletą tego systemu jest prostota i szybkość konfiguracji takiego modułu.

> Zauważ, że czasem podczas otwierania narzędzi deweloperskich (F12 w Firefox) strona może poprosić o zalogowanie przed udostępnieniem kodu strony. Wystarczy wtedy zamknąć to okno, i problem zniknie.

3. Następnie prześlij oba certyfikaty, klikając przycisk "Upload". Przekieruje nas to na podstronę, gdzie będziemy musieli wybrać plik i ustawić datę wykonania szkolenia. Upewnij się, że wybrana data jest mniejsza niż dzisiejsza o jeden dzień. Prześlij pliki z katalogu nadrzędnego projektu (cert1.pdf, cert2.pdf).

4. Po poprawnym przesłaniu plików, na stronie głównej powinien pojawić się przycisk "Make Request". Kliknij go i uzupełnij w następujący sposób:

   - Zaznacz wszystkie checkboxy.
   - Uzupełnij BID i BSID, wpisując 12345.
   - W każdym polu tekstowym wpisz losowy tekst.

5. Następnie kliknij "Submit".
6. Otwórz nowe okno **trybu prywatnego przeglądarki** i zaloguj się na konto: _manager:manager_password_.
7. Kliknij MENU (prawy górny róg) > "Manager review". W tabeli powinien być dostępny rekord użytkownika "User1".
8. Kliknij przycisk lupy znajdujący się w kolumnie "Verify". To standardowy widok zgłoszenia widziany przez managera.
9. Przewiń na sam dół strony i kliknij przycisk "Accept".

10. Ponieważ zgłoszenie zostało zweryfikowane przez managera, musi je jeszcze zaakceptować Administrator.

    - Zamknij prywatne okno i otwórz je ponownie, logując się na konto: _admin:admin_.
    - Kliknij MENU > "Admin requests" > "przycisk lupy" > "przycisk accept".

11. Wróć do okna przeglądarki, gdzie jest zalogowany użytkownik "user1". Powinieneś zobaczyć coś takiego:

![Poprawne wykonanie zadania](/img/finish.png)

12. Prześlij zdjęcie strony na Upel. Powinieneś zobaczyć ją w dokładnie taki sam sposób jak na zamieszczonym wyżej zdjęciu.

> Uwaga dla prowadzących: UID wyświetlające się na stronie jest unikalne i nie powinno się powtarzać :)

Czy po wstępnym przeanalizowaniu projektu udało Ci się zauważyć jakieś podatności lub rzeczy, które można by obrać za cel ataku? Jeśli nie, to się nie martw - pokażemy Ci 4 sposoby, w jakie można spróbować zaatakować tę aplikację :DD

> Aplikacja według naszych obliczeń posiada PONAD 20 różnych sposobów na zaatakowanie jej. W zadaniu 6 możesz przesłać dodatkowe rozwiązania, które udało Ci się odkryć!

## Zad 2

### Attak SQL injection.

1. Przywtórć projekt do stanu pierwotnego.
2. Zaloguj się na konto managera i otwórz requesta użytkownika "user8": [Request](http://localhost:7000/manager/review/request?user=8)

- Czy po wejściu na stronę requesta jesteś w stanie zidentyfikować, gdzie możemy dokonać ataku SQL injection?

3. W linku znajduje się parametr "user". Spróbuj wpisać coś innego niż 8. Jak strona reaguje na zmianę wprowadzonych wartości?

4. _Dzięki twojemu urokowi administrator serwer podziełił się częścią kodu php odpowiedzialnego za "sanityzację" zmiennych przesyłanych do serwera._

![Kod do zadania 2](/img/kod_zad2.png)

> testuj rozwiązania wpisując rózne wartości do zmiennej "user": "/manager/review/request?user="

> Zwróć uwagę, żę zmienna przesyłana w parmetrze "user" może być liczbą (o długości <8) albo stringiem (o długości >64)

> W celach testowych dodałem zmienną "debug" która pokazuję query wysyłane do serwera: "/manager/review/request?debug=1&user=".

> Na dobry początek spróbuj wykonać "SELECT \* FROM access;" - oczywiście musisz do tego dopisac coś jeszcze :DD

```php
$select_query = $this->db->prepare(
    'SELECT access.name FROM requests
        INNER JOIN access ON requests.eid = access.eid
    WHERE
        status=2 AND
        ((access.eid="'.$user_eid.'" AND access.manager_id=:eid) OR
        (access.delegated_manager_id =:eid_s AND access.eid="'.$user_eid.'"))'
);
```

#### Funkcja debug - ../request?debug=1".

![Funkcja debug](/img/debug_zad2.png)

5. Jak już uda Ci się wymyśleć w jaki sposób wysyłać zapytania do bazydanych to spróbuj usunąć tabele "access" z bazy danych ("DROP TABLE access;"). Po usunięciu tej tabeli i odświerzeniu strony powininneś zobaczyć następujący efekt:

6. Prześlij screen z przeprowadzonego ataku na upela wraz z linkiem zawierającym wstrzykniety kod SQL

![Poprawne wykonanie zadania 2](/img/finish2.png)

## Zad 3

### XSS injection.

## Zad 4

### File upload attack

1. Przywróć projekt do stanu pierwotnego.

2. Ponownie jak w zadaniu 1 zaloguj się używając konta: user1:user1_password. W pierwszym szkoleniu ponownie prześlij jeden z plików cer1.pdf / cert2.pdf.

   > Pamiętaj, że data musi być co najmniej wczorajsza!

3. Następnie w drugim kursie **prześlij plik podejrzany_plik_pdf.php** (go również znajdziesz w nadrzędnym katalogu projektu)

4. Wyślij i uzupełnij requesta (koniecznie zaznaczaj przynajmnjiej jeden "room"). Możesz to zrobić to w taki sam sposób jak w zadaniu 1.

5. Otwórz nowe okno przeglądarki w trybie prywatnym i zaloguj się na konto: manager:manager_password.

6. W "Manager review" ponownie znajdź rekord użytkownika "User1" i wejdź w niego.

   > MENU (prawy górny róg) >>> "Manager review" >>> rekord użytkownika "User1" >>> Ikona lupy.

7. Zatrzymaj się na tabeli reprezentującej dane dotyczące dwóch kursów. Reprezentuje ona wprowadzone przez Ciebie dane.
   Przesłane przez Ciebie pliki można otworzyć za pomocą ikon w ostatnim wierszu tabeli. Po kliknięciu ikony dotyczącej pierwszego kursu powinno
   rozpocząć się pobieranie pliku pdf. Natomiast kliknięcie ikony dotyczącej kursu "MATLAB Training for Building Access" powinno otworzyć nową kartę i wykonać kod php znajdujący się w przesłanym pliku.

![Tabela z przesłanymi danymi](/img/tabela.png)

9. Nowo otwarta karta będzie pusta. Spróbuj zmodyfikować plik 1_2.php znajdujący się w katalogu .docker/certificates/2 dodając do niego
   prostą instrukcję alert z js i odśwież stronę.

```
echo '<script>alert("Mandarynki i banany')</script>
```

> Zauważ, że pliki możesz otwierać również za pomocą URL

10. Okazuje się, że Maciek implementując aplikację pozwolił na wykonywanie kodu ukrytego w przesłanych plikach.
    Spróbujmy to jakoś wykorzystać.

11. Zajrzyj do [phpinfo](http://localhost:7000/phpinfo) i przejrzyj go. Jeśli nie wiesz czym jest phpinfo odzwiedz [manulal](https://www.php.net/manual/en/function.phpinfo.php). Zwróć uwagę, że nawet w komentarzach na tej stronie piszą, usunąć z niej pewne parametry prywatne użytownika (wyszykaj "$\_SERVER['AUTH_USER']"), zauważ że twórca tej strony o tym zapomniał, co wykorzystamy w naszym ataku.

    > phpinfo służy do wyświetlania szczegółowych informacji dotyczących konfiguracji i instalacji serwera PHP na danej maszynie.
    > Może zawierać informacje takie jak wersja PHP, konfiguracja serwera, zainstalowane rozszerzenia, etc,

12. Możesz tam zauważyć między innymi zmienne PHP_AUTH_PW, PHP_AUTH_USER, PHP_AUTH_RIGHTS, PHP_AUTH_EID. Wyswietlanie prywatnych danych użytkownika w tym jego hasła w plain text jest ogromny zagrożenie bezpieczeństwa i absolutnie nie można pozolic na to w środowisku produkcyjnym!!!

13. Ponownie zmodyfikuj plik 1_2.php, aby wyświetlał wartości **PHP_AUTH_EID** i **PHP_AUTH_RIGHTS**. Następnie odświerz [strone ceryfikatu](http://localhost:7000/download/cert/2?user=1)

14. Możnaby wywnioskować, że są to parametry które ustawia serwer przy uwierzytelnianiu, pytanie czy można je wykorzystać aby nasze konto "user1" uzyskało prawa managera? Zmień wspomniane zmienne PHP_AUTH_EID na '1' oraz PHP_AUTH_RIGHTS na 'MANAGER' i uruchom plik na końcie managera.

15. Powinieneś zobaczyć następującą wiadomość

![Tabela z przesłanymi danymi](/img/zad4_finish.png)

16. Prześlij zdjęcie Twojej wiadomości na UPEL następnie sprawdź czy user1 uzyskał prawa managera. Jeśli tak to poprawnie udało Ci sie wykonac to zadanie!

> Jeśli na koncie użytkownika nie widzisz przycisku MENU spróbuj otworzyć plik poprzez link: http://localhost:7000/download/cert/2?user=1

## Zad 6

### Dodatkowe wyzwania - (Trudniejsz) Ataki - Zadanie opcjonalene

> Dokumentcaj wykonania poniższych zadan opiera się na opisie słownym i prezentacji kodu który rozwiązuje dane zadanie.

A. Jak pozyskac informacje o o tabelach i ich strukturze w bazie danych posiadają wyłacznie dostęp do konta "user1"?

B. Przeanalizuj działanie funkcjonalności G-MODE - funkconalność pozwala zalogować się na konta innych uzytkowników po wpisaniu odpowiedniego ID użytkownika w formularz. Otwórz prywatne okno przeglądarki i zaloguj się na konto: maciek:maciek_password (koto to posiada prawo "g" kolumna "rights" tabela "access"). Następnie przejź do Menu > "Platform Manage" > User specific settings. Wpisz do forlmularza "User bind varible" wartość 1. Następnie klinij "Start". Od teraz powinnieneś móc być zalogowanym na konto użytkownika "user1". Żeby powrócić do swojego konta kliknij "STOP" w zakładce platform manage. Zadanie polega na tym żeby tak obejść zabezpieczenia aby z konta normalnego użytkownika być wstanie zalogować się na konto administratora.

C. Prześlij do serwera plik certyfikatu (jako plik .php) z konta user1 i następnie wykonaj go po nie przechodząc na inne konta. Wcześniej dało się to zrobić tylko z konta managera lub admina otwierają plik w review żadania użytkwnika. Jak zrobić to gdy nie ma już tej furtki?

D. Zaproponuj rozwiązanie które przyci się na do natychmiastowego nadania Ci uprawnień w momencie kiedy manager lub administrator wejdą na strone twojego requesta (MENU > "Manager review" > rekord użytkownika "User1". > lupa). Ten problem można rozwiazać na wiele sposóbów jednym z rozwiązań może być połaczenie dwórch ataków: XSS i File upload attack.

E. Zauważ, że gdy wysyłasz requesta zaznaczając tylko "Building" 1/2 lub/i 3 (+ zaznaczając do tego jeszcze wymagane pola) requesta musi zeryfikowac tylko administator. Gdy zaznaczysz do tego jezszcze przynajmniej jeden "room" twoje żadanie najpier idzie do managera, a dopiero później do admina (gdy manager zaakceptuje twoje zgłoszenie). Jak obejść tą funkcjonalność i wysłać zgłoszenie zawierające zaznaczone "room" + "building" w taki sposób aby request był wysłany tylko do admina, a nie do managera.
