# Opdracht 4 — Test software

**Project:** Syntec Camping — reserveringssysteem
**Werkprocessen:** B1-K1-W4 (Testen van software) en B1-K1-W5 (Aanpassen van software)
**Opgesteld door:** Ricardo Sas
**Peildatum:** 14 augustus 2026
**Testperiode:** maandag 10 t/m vrijdag 14 augustus 2026
**Getest op versie:** commit `289df96` (branch `master`)
**Versie document:** 1.0

**Teamleden**

| Code | Naam | Rol in de testfase |
|---|---|---|
| MV | Marco Verbist | Testcoördinatie, acceptatietesten, afstemming opdrachtgever |
| RS | Ricardo Sas | Ontwikkelaar, geautomatiseerde tests publieke site en boekingsflow |
| DV | Duncan Verdult | Ontwikkelaar, testomgeving, testdata, rechten en beheerschermen |

**Akkoord BPV-opleider**

| Naam | Functie | Datum | Handtekening |
|---|---|---|---|
| | | | |

---

## Inhoud

1. Inleiding en aanleiding
2. Testaanpak: testvormen, methodiek en framework
3. Bewijsstuk 1 — User stories
4. Bewijsstuk 2 — Testprotocol
5. Bewijsstuk 3 — Testplanning
6. Bewijsstuk 4 — Testrapport
7. Bewijsstuk 5 — Teamplanning herstel fouten
8. Conclusie en advies

---

## 1. Inleiding en aanleiding

Tijdens de realisatiefase (opdracht 3) is het reserveringssysteem van Syntec Camping opgeleverd. Er is tijdens het bouwen doorlopend getest, maar dat testen gebeurde ad hoc: een ontwikkelaar klikte na een wijziging het scherm door dat hij zojuist had aangepast. Daarmee zijn twee vragen niet beantwoord:

1. **Is de dekking volledig?** Er is nooit systematisch vastgelegd welk gewenst gedrag van het systeem wel en niet is gecontroleerd.
2. **Is het systeem bestand tegen regressie?** Bij elke volgende wijziging bestaat de kans dat functionaliteit die eerder werkte stilzwijgend kapotgaat, zonder dat iemand dat merkt.

Dat tweede risico is tijdens de bouw al bewezen. Toen de e-mailverzending werd omgezet naar de wachtrij (`ShouldQueue`) bleken zeven bestaande tests direct rood te slaan; zie bevinding BV-06 in hoofdstuk 6. Precies dat is de reden voor deze opdracht: een geautomatiseerde testsuite die dit soort neveneffecten zichtbaar maakt op het moment dat ze ontstaan, in plaats van bij de klant.

Dit document beschrijft de gekozen testaanpak, de gewenste gedragingen in de vorm van user stories, het protocol waarmee de tests worden uitgevoerd, de planning van het testproces, de resultaten en conclusies, en de met de opdrachtgever afgestemde planning voor het herstellen van de gevonden fouten.

**Scope.** Getest is de volledige webapplicatie: publieke website, boekingsflow, klantportaal, baliescherm en beheerschermen, inclusief e-mail, rechten en de AVG-functionaliteit. Buiten scope blijft de koppeling met Blookers.com; die is in opdracht 3 bewust niet gebouwd omdat er geen API beschikbaar is (zie HT-09). De betaalflow is een mock en wordt als zodanig getest.

---

## 2. Testaanpak: testvormen, methodiek en framework

### 2.1 Gekozen testvormen

| Testvorm | Wat wordt ermee gecontroleerd | Uitvoering |
|---|---|---|
| **Unit test** | Losse rekenlogica zonder database of HTTP, zoals het aantal nachten en de prijsberekening | Geautomatiseerd |
| **Integratietest (feature test)** | Een complete route van HTTP-verzoek tot database, e-mail en respons — inclusief middleware, validatie en rechten | Geautomatiseerd |
| **Statische analyse** | Typefouten, niet-bestaande eigenschappen en afwijkingen van de codeconventies, zonder de code te draaien | Geautomatiseerd |
| **Acceptatietest (handmatig)** | Zaken die een mens moet beoordelen: opmaak van e-mail, responsiveness, printweergave, uploads | Handmatig, volgens protocol |
| **Regressietest** | De volledige suite opnieuw, na elke wijziging | Geautomatiseerd |

Het zwaartepunt ligt bewust op de integratietest. In een Laravel-applicatie zit de bedrijfslogica grotendeels in de controllers, de validatie, de middleware en de Eloquent-modellen; die werken alleen samen. Een unit test op een controller in isolatie zou vooral de mock testen en niet het gedrag dat de gebruiker ervaart.

### 2.2 Testmethodiek

De methodiek is **risicogestuurd en user-story-gedreven**:

1. Per rol (bezoeker, klant, baliemedewerker, beheerder) is het gewenste gedrag opgeschreven als user story met acceptatiecriteria (hoofdstuk 3).
2. Elke user story krijgt minimaal één geautomatiseerde test die het "happy path" bewijst.
3. Bij elke user story wordt daarnaast expliciet het **faalpad** getest: ontbrekende invoer, ongeldige invoer en een gebruiker zonder rechten. Rechten zijn het grootste risico in dit systeem, omdat één vergeten middleware direct klantgegevens blootlegt.
4. Waar met waarden wordt gewerkt (datums, aantallen, bedragen) is getest op **grenswaarden**: een verblijf dat begint op de dag dat een ander verblijf eindigt, een einddatum vóór de startdatum, een startdatum in het verleden, de maximale hoeveelheid getoonde items.
5. Wat niet zinvol of niet betaalbaar te automatiseren is, is opgenomen als handmatig testgeval met een motivatie (hoofdstuk 4).

### 2.3 Keuze van het testframework

**Gekozen: PHPUnit 11.5 met de HTTP-testlaag van Laravel 12.** Ondersteunend: Larastan/PHPStan (level 5) voor statische analyse en Laravel Pint voor de codeconventies.

Motivatie:

- Eén test dekt de hele keten. Met `$this->actingAs($user)->getJson(route(...))` wordt de echte route, de echte middleware, de echte validatie en de echte database geraakt. Een browserframework als Selenium test alleen wat het scherm laat zien en kan bijvoorbeeld niet controleren of een e-mail daadwerkelijk in de wachtrij is gezet.
- Het framework kan e-mail, wachtrij en tijd vervangen door een testdubbel (`Mail::fake()`, `Queue::fake()`, `travelTo()`). Daarmee zijn scenario's testbaar die in een browser onmogelijk zijn, zoals "een verblijf van vorig jaar".
- De suite draait in **10,5 seconde**. Een gelijkwaardige Selenium-suite kost tientallen minuten en wordt daardoor in de praktijk niet meer voor elke commit gedraaid — dan verdwijnt precies de regressiebewaking waar het ons om te doen is.
- Het draait zonder browser, zonder ChromeDriver en zonder extra server, en dus op de laptop van elke ontwikkelaar en later ook in een build-straat.

**Overwogen en niet gekozen:**

| Alternatief | Reden om niet te kiezen |
|---|---|
| Selenium / Laravel Dusk | Test alleen de buitenkant, traag, breekbaar bij elke opmaakwijziging. Voor de acht scenario's waar een browser écht nodig is, is handmatig testen bij dit teamformaat goedkoper (zie hoofdstuk 4, HT-01 t/m HT-08). |
| Cucumber / Behat | De extra Gherkin-laag levert winst op als een niet-technische opdrachtgever zelf scenario's schrijft. Dat is hier niet het geval; de opdrachtgever levert wensen aan in gesprek. De onderhoudslast weegt niet op tegen de winst. |
| Vitest / Vue Test Utils | De Vue-componenten bevatten nauwelijks eigen logica; ze tonen data die de server via Inertia meestuurt. Die data wordt al gecontroleerd met `assertInertia`. |

### 2.4 Testomgeving, middelen en testdata

**Omgeving** (vastgelegd in [phpunit.xml](../phpunit.xml), zodat iedere ontwikkelaar identiek test):

| Onderdeel | Instelling | Reden |
|---|---|---|
| `APP_ENV` | `testing` | Scheidt de testconfiguratie van ontwikkel- en productieconfiguratie |
| `DB_CONNECTION` / `DB_DATABASE` | `sqlite` / `:memory:` | Elke test start met een lege database in het geheugen; snel en geen vervuiling van de ontwikkeldatabase |
| `MAIL_MAILER` | `array` | Er wordt tijdens het testen nooit echte post verstuurd |
| `QUEUE_CONNECTION` | `sync` | Wachtrijtaken draaien direct, zodat het resultaat in dezelfde test controleerbaar is |
| `CACHE_STORE` / `SESSION_DRIVER` | `array` | Geen gedeelde staat tussen tests |
| `BCRYPT_ROUNDS` | `4` | Wachtwoorden hashen sneller; alleen in de testomgeving aanvaardbaar |

Elke testklasse gebruikt de trait `RefreshDatabase`, waardoor iedere test in een transactie draait die daarna wordt teruggedraaid. Tests zijn daarmee onafhankelijk van elkaar en van de volgorde waarin ze draaien.

**Middelen:** PHP 8.2.12, Composer, PHPUnit 11.5.55, Larastan/PHPStan 3, Laravel Pint 1, Git/GitHub voor versiebeheer en het vastleggen van bevindingen.

**Testdata:** de tests maken hun eigen data aan met de factories (`UserFactory`, `CustomerFactory`, `LocationFactory`, `ArrangementFactory`, `NewsFactory`). Er is bewust geen vaste testdatabase; data die in een test wordt gebruikt staat in die test, zodat bij een rode test direct zichtbaar is met welke gegevens het misging. Daarnaast zijn de seeders zélf getest (`LocationsSeederTest`, `CustomerSeederTest`, `ArrangementsSeederTest`, `NewsSeederTest`, `DatabaseSeederTest`), omdat de demo-omgeving die de opdrachtgever te zien krijgt daarop draait.

> **Aandachtspunt uit de testfase.** Dat factories vrij zijn in te vullen is een kracht én een risico: een test die zijn eigen testdata aanpast tot de code slaagt, bewijst niets. Bevinding BV-01 is precies zo'n geval en is de belangrijkste vondst van deze testronde.

---

## 3. Bewijsstuk 1 — User stories

De stories zijn afgeleid uit drie bronnen: het routeoverzicht met de rechtenmatrix (`php artisan route:list --except-vendor -v`), het ontwerpdocument uit opdracht 2 (use-case- en sequencediagram) en de klachtenlijst van de opdrachtgever uit opdracht 1.

Vier rollen, conform `RolesAndPermissionsSeeder`: **Bezoeker** (niet ingelogd), **Klant** (geregistreerd), **Baliemedewerker** en **Beheerder**. Beheerder erft alle rechten van Baliemedewerker; Klant erft de publieke rechten van Bezoeker.

De kolom *Dekking* verwijst naar de testklasse die de story bewijst. `—` betekent: niet geautomatiseerd, zie de verwijzing naar het handmatige testgeval of naar een bevinding.

### 3.1 Bezoeker

| ID | User story | Acceptatiecriteria | Dekking |
|---|---|---|---|
| US-01 | Als bezoeker wil ik op de homepage de mooiste accommodaties zien, zodat ik meteen een indruk krijg van de camping. | Alleen locaties met `advertised` én `active` worden getoond; maximaal vier; elk met foto. | `HomepageLocationsTest` (4) |
| US-02 | Als bezoeker wil ik alle beschikbare accommodaties kunnen bekijken, zodat ik kan kiezen wat bij mij past. | Alle actieve locaties staan op de locatiepagina; inactieve niet. | `HomepageLocationsTest` |
| US-03 | Als bezoeker wil ik het nieuws van de camping lezen, zodat ik weet wat er speelt. | Alleen gepubliceerde en actieve artikelen; nieuwste eerst; maximaal vijf op de homepage; concepten onzichtbaar. | `NewsPageTest` (9) |
| US-04 | Als bezoeker wil ik de site in het Nederlands of Engels kunnen lezen, zodat ook buitenlandse gasten kunnen boeken. | De keuze blijft in de sessie staan; een onbekende taal wordt geweigerd; zonder vertaalbestand valt de site terug op de Nederlandse brontekst. | `LocaleSwitchTest` (4) |
| US-05 | Als bezoeker wil ik een vraag kunnen stellen via een contactformulier, zodat ik contact kan opnemen zonder te bellen. | Naam, e-mail en bericht zijn verplicht; een ongeldig e-mailadres wordt geweigerd; het bericht komt bij de camping binnen. | `ContactFormTest` (3) |
| US-06 | Als bezoeker wil ik zien of een accommodatie vrij is in de periode die ik wil, zodat ik geen boeking doe die toch niet kan. | Een vrije periode meldt beschikbaar; een bezette periode meldt bezet mét reden; een startdatum in het verleden wordt geweigerd; een geannuleerde reservering blokkeert niets; een verblijf dat begint op de vertrekdag van een ander is beschikbaar. | `LocationAvailabilityTest` (12) |
| US-07 | Als bezoeker wil ik direct de prijs van mijn verblijf zien, zodat ik niet hoef te rekenen. | Aantal nachten = einddatum − startdatum; prijs = nachtprijs × nachten; beide datums verplicht; einddatum vóór startdatum wordt geweigerd; onbekende locatie wordt geweigerd. | `CalculationsTest` (5) |
| US-08 | Als bezoeker wil ik online een accommodatie kunnen reserveren, zodat ik niet hoef te bellen. | De reservering wordt opgeslagen met klant, periode en prijs; een bezette locatie wordt geweigerd en er wordt dan géén klant aangemaakt. | `LocationAvailabilityTest`, `RoutePermissionsTest` |
| US-09 | Als bezoeker wil ik na het boeken een bevestiging per e-mail ontvangen, zodat ik zeker weet dat de reservering binnen is. | De bevestigingsmail gaat naar het opgegeven adres en bevat de juiste reserveringsgegevens; de reservering wordt gemarkeerd als "mail verstuurd"; een geweigerde boeking stuurt geen mail; een falende mailserver mag de boeking niet ongedaan maken. | `BookingMailSentTest` (6), `MailRenderTest` |
| US-10 | Als bezoeker wil ik tijdens het boeken meteen een account kunnen aanmaken, zodat ik mijn reservering later kan terugvinden. | De nieuwe gebruiker krijgt de rol klant en een e-mail met een link om een wachtwoord in te stellen; wie geen account wil krijgt geen mail; een bestaand e-mailadres levert geen tweede mail op. | `RegistrationTest`, `NewAccountMailSentTest` (3) |

### 3.2 Klant

| ID | User story | Acceptatiecriteria | Dekking |
|---|---|---|---|
| US-11 | Als klant wil ik kunnen inloggen en uitloggen, zodat mijn gegevens afgeschermd zijn. | Inloggen met juiste gegevens slaagt, met een verkeerd wachtwoord niet; uitloggen beëindigt de sessie. | `Auth\AuthenticationTest` (4) |
| US-12 | Als klant wil ik mijn wachtwoord opnieuw kunnen instellen als ik het vergeten ben. | Een resetlink wordt in de wachtrij gezet; het scherm opent met een geldig token; het wachtwoord wordt daadwerkelijk gewijzigd. | `Auth\PasswordResetTest` (4), `Auth\PasswordUpdateTest` |
| US-13 | Als klant wil ik mijn e-mailadres kunnen laten verifiëren, zodat de camping mij zeker kan bereiken. | Verificatie met de juiste hash slaagt, met een onjuiste hash niet. | `Auth\EmailVerificationTest` (3) |
| US-14 | Als klant wil ik mijn gegevens kunnen aanpassen of mijn account kunnen verwijderen, zodat ik zelf de regie heb (AVG). | Gegevens worden opgeslagen; een ongewijzigd e-mailadres verliest zijn verificatie niet; verwijderen vraagt om het wachtwoord. | `ProfileTest` (5) |
| US-15 | Als klant wil ik na inloggen mijn eigen reserveringen zien en niet die van anderen. | Een klant komt op het klantdashboard terecht; een gebruiker zonder rol wordt geweigerd. | `RoutePermissionsTest` |
| US-16 | Als klant wil ik mijn reservering online kunnen betalen, zodat ik dat niet aan de balie hoef te doen. | De betaalpagina opent op GUID; een onbekende GUID geeft "niet gevonden"; een al betaalde reservering kan de pagina niet opnieuw openen; een tweede betaling wordt geweigerd. | `PaymentPageTest` (6) |
| US-17 | Als klant wil ik een bevestiging van mijn betaling ontvangen, zodat ik een bewijs heb. | Na betaling wordt de reservering op betaald gezet en gaat er precies één betaalbevestiging uit. | `PaymentPageTest`, `ArrangementPaymentConfirmationTest` |

### 3.3 Baliemedewerker

| ID | User story | Acceptatiecriteria | Dekking |
|---|---|---|---|
| US-18 | Als baliemedewerker wil ik alle reserveringen in een overzicht zien, met zoeken, sorteren en paginering, zodat ik snel de juiste boeking vind. | Vaste hoeveelheid per pagina; zoeken op klantnaam, klant-e-mail en locatie; sorteren op prijs en klantnaam; een onbekende sortering valt terug op aankomstdatum; een onbekende richting wordt geweigerd; het zoeken blijft binnen de gekozen status; de gebruikte filters komen terug op het scherm. | `ArrangementOverviewTest` (11) |
| US-19 | Als baliemedewerker wil ik de status van een reservering kunnen wijzigen, zodat ik gasten kan inchecken. | Alleen statussen uit de enum worden geaccepteerd; de bijgewerkte reservering komt terug in de respons; verder verandert er niets aan de reservering; een baliemedewerker mag inchecken maar niet goed- of afkeuren; elke status hangt aan een bestaand recht. | `ArrangementStatusUpdateTest` (11) |
| US-20 | Als baliemedewerker wil ik een klant kunnen opzoeken op e-mailadres en telefoonnummer, zodat ik hem aan de balie kan helpen. | De klant wordt gevonden op de combinatie e-mailadres + telefoonnummer; een klant mag dit niet. | `RoutePermissionsTest` — **zie BV-01: de test slaagt, de functie werkt niet met echte data** |
| US-21 | Als baliemedewerker wil ik een klant kunnen aanmaken, zodat ik telefonische boekingen kan invoeren. | De klant wordt opgeslagen; een klant of bezoeker mag dit niet. | `RoutePermissionsTest` |
| US-22 | Als baliemedewerker wil ik een betaling aan de balie kunnen registreren, zodat de administratie klopt. | De betaling wordt vastgelegd en de klant krijgt bericht; een tweede bevestiging stuurt geen tweede mail; de boekingsstatus verandert niet mee; een onbekende reservering wordt geweigerd; bezoeker en klant mogen dit niet. | `ArrangementPaymentConfirmationTest` (7) |
| US-23 | Als baliemedewerker wil ik per maand zien welke accommodaties bezet zijn, zodat ik telefonisch advies kan geven. | Zonder parameter de huidige maand; met parameter de gekozen maand; een reservering die de hele maand beslaat telt mee; geannuleerde en afgewezen reserveringen niet; een onjuiste maandnotatie wordt geweigerd. | `DashboardCalendarTest` (5) |

### 3.4 Beheerder

| ID | User story | Acceptatiecriteria | Dekking |
|---|---|---|---|
| US-24 | Als beheerder wil ik accommodaties kunnen beheren, zodat het aanbod actueel blijft. | Alleen de beheerder mag het locatiebeheer openen en locaties opslaan; een baliemedewerker niet. | `RoutePermissionsTest` |
| US-25 | Als beheerder wil ik nieuwsberichten kunnen plaatsen en als concept kunnen bewaren, zodat ik ze kan voorbereiden. | Het beheerscherm toont ook concepten; de publieke pagina niet; zonder recht is het scherm gesloten. | `NewsPageTest`, `RoutePermissionsTest` |
| US-26 | Als beheerder wil ik dat elke pagina en elk endpoint afgeschermd is met het juiste recht, zodat gegevens niet uitlekken. | De middleware laat een gebruiker mét het recht door en blokkeert een gebruiker zonder; getest per route en per rol, inclusief gebruikers zonder rol en niet-ingelogde bezoekers. | `PermissionMiddlewareTest` (2), `RoutePermissionsTest` (27) |
| US-27 | Als beheerder wil ik dat een accommodatie nooit dubbel geboekt kan worden, zodat er geen twee gezinnen voor dezelfde deur staan. | Overlappende reserveringen worden geweigerd op zowel het API-endpoint als de boekingspagina; een reservering blokkeert zichzelf niet bij het wijzigen. | `LocationAvailabilityTest`, `ArrangementsSeederTest` |
| US-28 | Als beheerder wil ik dat klantgegevens automatisch worden geanonimiseerd zeven jaar na het laatste verblijf, zodat wij aan de AVG voldoen. | Klanten die zeven jaar niets meer geboekt hebben worden geanonimiseerd; klanten die nog wél boeken blijven ongemoeid; de opdracht draait automatisch volgens schema. | Bij de testronde van 13-08: **geen** (zie BV-02, BV-03 en BV-09). Sinds 16-08: `AnonymizeCustomersTest` (10) |
| US-29 | Als beheerder wil ik dat de klant naar buiten toe met een GUID wordt aangeduid en niet met een oplopend nummer, zodat gegevens niet te raden zijn. | Elke tabel vult automatisch een geldige UUID; elk record krijgt een eigen GUID; de primaire sleutel blijft intern een oplopend getal. | `GuidColumnTest` (5), `ModelGuidTest` (2) |
| US-30 | Als beheerder wil ik een demo-omgeving met realistische gegevens, zodat ik het systeem kan tonen en er zonder risico in kan oefenen. | Seeden vult elke tabel; minimaal vijf locaties met complete gegevens; geen "lorem ipsum"; unieke e-mailadressen; geen dubbel geboekte locatie; verleden boekingen staan op afgerond. | `DatabaseSeederTest`, `LocationsSeederTest` (5), `CustomerSeederTest` (4), `ArrangementsSeederTest` (5), `NewsSeederTest` (3) |

**Totaal: 30 user stories, waarvan er bij de testronde van 13 augustus 29 geautomatiseerd waren afgedekt en 1 (US-28) niet. Sinds het herstel van 16 augustus zijn alle 30 afgedekt; zie 6.6.**

---

## 4. Bewijsstuk 2 — Testprotocol

Dit protocol beschrijft hoe iedere ontwikkelaar de tests uitvoert. Het is opgenomen in de README van het project, zodat een nieuwe teamgenoot het zonder uitleg kan volgen.

### 4.1 Eenmalige voorbereiding

```bash
composer install
npm install
php artisan key:generate
```

Er is verder niets nodig: de testsuite gebruikt een SQLite-database in het geheugen en verstuurt geen e-mail. Een draaiende webserver, database-server of wachtrij-worker is voor de geautomatiseerde tests **niet** vereist.

### 4.2 De geautomatiseerde tests starten

| Doel | Commando |
|---|---|
| Volledige suite | `php artisan test --compact` |
| Volledige suite met schone configuratiecache | `composer test` |
| Eén testbestand | `php artisan test tests/Feature/BookingMailSentTest.php` |
| Eén test op naam | `php artisan test --filter=test_a_booking_sends_a_confirmation_mail_to_the_customer` |
| Statische analyse | `vendor/bin/phpstan analyse` |
| Controle codeconventies | `vendor/bin/pint --test` |
| Codeconventies automatisch herstellen | `vendor/bin/pint` |

### 4.3 Wanneer wordt er getest

| Moment | Wat | Door wie |
|---|---|---|
| Vóór elke commit | De testbestanden die bij de gewijzigde code horen (`--filter`) | De ontwikkelaar zelf |
| Vóór elke push naar `master` | De volledige suite + PHPStan + Pint | De ontwikkelaar zelf |
| Bij elke samenvoeging van werk | De volledige suite | De ontwikkelaar die samenvoegt |
| Aan het eind van elke sprint | De volledige suite + het handmatige testprotocol uit 4.4 | MV |

**Definition of done voor een wijziging:** de volledige suite is groen, PHPStan geeft geen nieuwe meldingen, Pint meldt geen afwijkingen, en het nieuwe gedrag is gedekt door minimaal één nieuwe of aangepaste test.

**Bij een rode test geldt: de test heeft gelijk tot het tegendeel bewezen is.** Een test wordt nooit aangepast om hem groen te krijgen zonder dat is vastgesteld dat de verwachting in de test onjuist was. Is dat wél het geval, dan wordt de reden vastgelegd in de commitboodschap. Zie BV-06, waar dit precies zo is toegepast.

### 4.4 Handmatige testgevallen

Niet alles is zinvol te automatiseren. Hieronder staat per geval wat er handmatig wordt gecontroleerd en waarom dat niet is geautomatiseerd. Deze gevallen worden aan het eind van elke sprint doorlopen; het resultaat wordt in de laatste kolom afgetekend.

| ID | Testgeval | Stappen | Verwacht resultaat | Waarom handmatig | Resultaat 12-08-2026 |
|---|---|---|---|---|---|
| HT-01 | Opmaak van de uitgaande e-mail | Start `php artisan queue:work`, vul in `.env` een SMTP-testadres in, plaats een boeking, open de mail in Outlook en Gmail | Logo zichtbaar, gegevens compleet, knoppen werken, geen kapotte opmaak | Een geautomatiseerde test kan de HTML controleren, maar niet hoe een mailclient die weergeeft | Akkoord |
| HT-02 | Volledige boekingsflow in de browser | Doorloop als bezoeker: locatie kiezen → periode → prijs → gegevens → bevestigen | Elke stap volgt logisch, geen JavaScript-fouten in de console | Beoordeling van de gebruikerservaring is mensenwerk | Akkoord |
| HT-03 | Betaalflow (mock) | Open de betaallink uit de mail, betaal, open de link nogmaals | Tweede poging wordt geweigerd met een nette melding | De echte betaaldienst ontbreekt; alleen de mock is geautomatiseerd getest | Akkoord |
| HT-04 | Taalwissel visueel | Wissel op elke publieke pagina naar Engels | Geen onvertaalde teksten, geen kapotte opmaak door langere woorden | Ontbrekende vertalingen zijn geautomatiseerd te vinden, afbreukschade in de opmaak niet | Akkoord |
| HT-05 | Uploaden van foto's | Upload een foto bij een locatie en bij een nieuwsbericht | Foto verschijnt op de publieke pagina | Vereist `storage:link` en het echte bestandssysteem | Akkoord |
| HT-06 | Printweergave van één reservering | Open een reservering en druk op printen | Alle gegevens passen op één pagina, geen navigatie in de afdruk | Printopmaak is niet uit te lezen in een test | Akkoord |
| HT-07 | Responsiveness van het dashboard | Bekijk het baliescherm op 1920, 1024 en 375 pixels breed | Tabellen blijven leesbaar, knoppen bereikbaar | Vergt visuele beoordeling | Aandachtspunt: het reserveringsoverzicht schuift horizontaal op 375 px. Geaccepteerd, de balie werkt op een desktop |
| HT-08 | Anonimiseren van klantgegevens | Draai `php artisan customers:anonymize` op een kopie van de productiedatabase | Alleen klanten zonder verblijf in de laatste zeven jaar worden geanonimiseerd | Onomkeerbare bewerking; nooit op de echte database uitvoeren | **Afgekeurd — zie BV-02** |
| HT-09 | Koppeling Blookers.com | — | — | Buiten scope: Blookers biedt geen API en er is geen testomgeving beschikbaar (zie opdracht 2) | Niet uitgevoerd |

### 4.5 Vastleggen van bevindingen

Elke bevinding wordt vastgelegd als issue op GitHub met: nummer, korte omschrijving, betrokken user story, stappen om te reproduceren, verwacht en werkelijk resultaat, prioriteit (hoog/midden/laag) en een schatting in uren. Hoofdstuk 6 is de samenvatting van die lijst op de peildatum.

---

## 5. Bewijsstuk 3 — Testplanning

Het testproces was in de oorspronkelijke planning uit opdracht 1 niet als aparte fase opgenomen; testen zat verstopt in de bouwuren. Dat is met deze planning rechtgezet: het opzetten van de testomgeving, het schrijven van user stories en het vertalen daarvan naar tests zijn nu eigen taken met een eigen doorlooptijd.

### 5.1 Taakverdeling en tijdlijn testfase

| ID | Taak | Wie | Uren | Wanneer |
|---|---|---|---|---|
| T-01 | User stories opstellen op basis van de rechtenmatrix, het ontwerp en de klachtenlijst K1 t/m K6 | RS | 4 | ma 10 aug |
| T-02 | User stories reviewen en met de opdrachtgever afstemmen | MV | 2 | ma 10 aug |
| T-03 | Testframework kiezen, motiveren en de testomgeving inrichten (`phpunit.xml`, in-memory database, testdubbel voor mail, PHPStan, Pint) | DV | 4 | ma 10 aug |
| T-04 | Testdata inrichten: factories en seeders geschikt maken voor gebruik in tests | DV | 4 | di 11 aug |
| T-05 | US-01 t/m US-10 vertalen naar tests (publieke site, beschikbaarheid, boekingsflow) | RS | 8 | di 11 – wo 12 aug |
| T-06 | US-18 t/m US-30 vertalen naar tests (balie, beheer, rechten, seeders) | DV | 8 | wo 12 – do 13 aug |
| T-07 | US-11 t/m US-17 vertalen naar tests (account, e-mail, betaling) | MV | 6 | di 11 – wo 12 aug |
| T-08 | Statische analyse inrichten en over de volledige codebase draaien | DV | 2 | do 13 aug |
| T-09 | Testprotocol schrijven en in de README opnemen | MV | 2 | wo 12 aug |
| T-10 | Handmatige acceptatietests HT-01 t/m HT-08 uitvoeren | MV (2) + RS (2) | 4 | wo 12 aug |
| T-11 | Volledige testronde draaien en testrapport opstellen | RS | 3 | do 13 aug |
| T-12 | Bevindingen analyseren, schatten en met de opdrachtgever afstemmen | MV | 3 | do 13 aug |
| T-13 | Herstelplanning opstellen en in de tijdlijn verwerken | MV | 2 | vr 14 aug |
| | **Totaal testfase** | | **52** | |

**Verdeling per teamlid:** MV 17 uur, RS 17 uur, DV 18 uur.

### 5.2 Aangepaste tijdlijn

| Periode | Oorspronkelijke planning (opdracht 1 en 3) | Aangepaste planning |
|---|---|---|
| 29 juni – 16 juli | Realisatie | Ongewijzigd |
| 10 – 14 augustus | Oplevering aan de opdrachtgever | **Testfase** (T-01 t/m T-13): user stories, testomgeving, tests schrijven, handmatig testen, rapport en afstemming |
| 17 – 20 augustus | Herstelweek, uitloop | **Herstelweek** met de bevindingen uit dit rapport (zie hoofdstuk 7) |
| 21 augustus | — | Oplevering aan de opdrachtgever |

De oplevering schuift daarmee naar 21 augustus. Die verschuiving is op 13 augustus met de opdrachtgever besproken en akkoord bevonden; de motivering staat in 7.3. De herstelweek van 17 t/m 20 augustus stond al in de planning en wordt nu gevuld met de bevindingen uit dit testrapport in plaats van met open uitloop.

### 5.3 Urenverantwoording

| Post | MV | RS | DV | Totaal |
|---|---|---|---|---|
| Testfase (hoofdstuk 5.1) | 17 | 17 | 18 | 52 |
| Herstel van bevindingen (hoofdstuk 7.1) | 1 | 5,5 | 10 | 16,5 |
| **Totaal testen en herstellen** | **18** | **22,5** | **28** | **68,5** |

Deze 68,5 manuren komen boven op de uren die in het realisatiedocument van opdracht 3 zijn verantwoord.

---

## 6. Bewijsstuk 4 — Testrapport

### 6.1 Uitvoeringsgegevens

| | |
|---|---|
| Datum uitvoering | 13 augustus 2026 |
| Geteste versie | commit `289df96`, branch `master` |
| Omgeving | PHP 8.2.12, PHPUnit 11.5.55, SQLite in-memory, `APP_ENV=testing` |
| Commando | `php artisan test --compact` |
| Uitgevoerd door | RS |

### 6.2 Resultaat geautomatiseerde tests

| | Aantal |
|---|---|
| Testklassen | 27 |
| Uitgevoerde tests | **173** |
| Assertions | **circa 1.400** |
| Geslaagd | 173 |
| Gefaald | 0 |
| Overgeslagen | 0 |
| Doorlooptijd | 10,49 seconden |

**Tests per testklasse**

| Testklasse | Tests | Dekt user story |
|---|---|---|
| `RoutePermissionsTest` | 27 | US-15, US-20, US-21, US-24, US-25, US-26 |
| `LocationAvailabilityTest` | 12 | US-06, US-08, US-27 |
| `ArrangementOverviewTest` | 11 | US-18 |
| `ArrangementStatusUpdateTest` | 11 | US-19 |
| `NewsPageTest` | 9 | US-03, US-25 |
| `ArrangementPaymentConfirmationTest` | 7 | US-22 |
| `BookingMailSentTest` | 6 | US-09 |
| `MailRenderTest` | 6 | US-09, US-10, US-12, US-17 |
| `PaymentPageTest` | 6 | US-16, US-17 |
| `ArrangementsSeederTest` | 5 | US-27, US-30 |
| `CalculationsTest` | 5 | US-07 |
| `DashboardCalendarTest` | 5 | US-23 |
| `GuidColumnTest` | 5 | US-29 |
| `LocationsSeederTest` | 5 | US-30 |
| `ProfileTest` | 5 | US-14 |
| `Auth\AuthenticationTest` | 4 | US-11 |
| `Auth\PasswordResetTest` | 4 | US-12 |
| `Auth\RegistrationTest` | 4 | US-10, US-11 |
| `CustomerSeederTest` | 4 | US-30 |
| `HomepageLocationsTest` | 4 | US-01, US-02 |
| `LocaleSwitchTest` | 4 | US-04 |
| `Auth\EmailVerificationTest` | 3 | US-13 |
| `Auth\PasswordConfirmationTest` | 3 | US-11 |
| `ContactFormTest` | 3 | US-05 |
| `NewAccountMailSentTest` | 3 | US-10 |
| `NewsSeederTest` | 3 | US-30 |
| `Auth\PasswordUpdateTest` | 2 | US-12 |
| `ModelGuidTest` | 2 | US-29 |
| `PermissionMiddlewareTest` | 2 | US-26 |
| `DatabaseSeederTest` | 1 | US-30 |
| `ExampleTest` (Feature en Unit) | 2 | — (restant van de standaardinstallatie, zie BV-07) |

### 6.3 Resultaat statische analyse

| Gereedschap | Resultaat |
|---|---|
| PHPStan (Larastan) level 5 | **1 melding** — `app/Console/Commands/AnonymizeCustomers.php:41`, toegang tot een niet-bestaande eigenschap `Customer::$arrangement_created_at` (zie BV-04) |
| Laravel Pint | **2 bestanden wijken af** van de codeconventies: `AnonymizeCustomers.php` (`concat_space`) en `Customer.php` (`class_attributes_separation`, `concat_space`, `unary_operator_spaces`, `braces_position`, `not_operator_with_successor_space`) (zie BV-05) |

### 6.4 Bevindingen

| ID | Bevinding | User story | Prioriteit | Status |
|---|---|---|---|---|
| BV-01 | Klant is niet op te zoeken aan de balie door tegenstrijdige telefoonnummervalidatie | US-20 | **Hoog** | **Hersteld** (ticket 45, hertest 16-08-2026) |
| BV-02 | `customers:anonymize` anonimiseert klanten die nog wél actief zijn | US-28 | **Hoog** | **Hersteld** (ticket 43, hertest 16-08-2026) |
| BV-03 | De AVG-functionaliteit heeft geen enkele geautomatiseerde test | US-28 | Midden | **Hersteld** (ticket 44, hertest 16-08-2026) |
| BV-04 | PHPStan-melding: niet-bestaande eigenschap in `AnonymizeCustomers` | US-28 | Midden | **Hersteld** (ticket 46, hertest 16-08-2026) |
| BV-05 | Twee bestanden voldoen niet aan de codeconventies | — | Laag | **Hersteld** (ticket 47, hertest 16-08-2026) |
| BV-06 | Regressie door omzetten van e-mail naar de wachtrij | US-09, US-12, US-17 | Hoog | **Gesloten** |
| BV-07 | Lege voorbeeldtests uit de standaardinstallatie staan nog in de suite | — | Laag | Open |
| BV-08 | Restrisico: het wachtrij-pad wordt door geen enkele test end-to-end bewezen | US-09 | Laag | Bewust niet ingepland |
| BV-09 | `anonymize()` schrijft naar een kolom die niet bestaat: de opdracht loopt vast en de postcode blijft staan | US-28 | **Hoog** | Gevonden én hersteld op 16-08-2026 tijdens ticket 43 |

---

#### BV-01 — Klant is niet op te zoeken aan de balie *(Hoog)*

**Betreft:** US-20, `CustomerController::find`, [CustomerController.php:47](../app/Http/Controllers/CustomerController.php)

**Omschrijving.** Het endpoint `api.customers.find` valideert het telefoonnummer met `required|string|min:10|max:10`: exact tien tekens. Klanten worden echter opgeslagen in de notatie `06-########`, elf tekens inclusief het koppelteken. Daardoor kan een baliemedewerker een bestaande klant nooit vinden:

- typt hij het nummer zoals het in het systeem staat (`06-12345678`, elf tekens), dan wordt het verzoek geweigerd met een validatiefout;
- typt hij tien cijfers (`0612345678`), dan komt het verzoek door de validatie maar levert de zoekopdracht geen resultaat op, omdat de opgeslagen waarde een koppelteken bevat.

Bovendien is de behandeling van het nummer inconsistent: bij het opslaan worden spaties wél verwijderd (`str_replace(' ', '', …)`), maar koppeltekens niet, en daar geldt `max:50` in plaats van `max:10`.

**Reproductie.** `php artisan migrate:fresh --seed`, inloggen als baliemedewerker, en een geseede klant opzoeken op e-mailadres en telefoonnummer.

**Waarom de test dit niet heeft gevonden — de belangrijkste les van deze testronde.** De test `test_a_receptionist_may_look_a_customer_up` is groen. Die test maakt echter zijn eigen klant aan en overschrijft daarbij het telefoonnummer met `'0624815903'` — precies tien tekens. De test bewijst daarmee alleen dat de baliemedewerker het *recht* heeft om te zoeken, niet dat het zoeken werkt met gegevens zoals die in het systeem staan. **De testdata is aangepast aan de code in plaats van aan de werkelijkheid.** Dit is bij het opstellen van dit rapport het uitgangspunt geworden voor het herschrijven van soortgelijke tests.

**Voorstel voor verbetering.** Één notatie kiezen en die op één plek afdwingen. Voorstel: het telefoonnummer bij het opslaan normaliseren door spaties, koppeltekens en de landcode te verwijderen, en de validatie op alle drie de plaatsen gelijktrekken naar `digits_between:10,15`. Aanvullend de bestaande gegevens in de database eenmalig omzetten.

**Herstel (ticket 45, uitgevoerd op 16 augustus 2026 door DV).** Het voorstel is ongewijzigd doorgevoerd. De normalisatie is bewust in het model gelegd en niet in de schermen, zodat geen enkel scherm hem kan overslaan:

| Bestand | Wijziging |
|---|---|
| `app/Models/Customer.php` | Nieuwe methode `normalisePhoneNumber()`: haalt alles weg wat geen cijfer is en zet een leidende `+` om naar `00`. Een mutator op `phone_number` past die toe op alles wat naar de database gaat; `findByEmailAndPhoneNumber()` past hem toe op waar op gezocht wordt. |
| `app/Http/Controllers/CustomerController.php` | `find()` normaliseert de invoer vóór de validatie en valideert daarna op `digits_between:10,15` in plaats van `min:10\|max:10`. De losse `str_replace(' ', '', …)` in `store()` is vervallen; die zat er alleen omdat de normalisatie ontbrak. |
| `app/Http/Controllers/BookingController.php` | Idem: de losse spatie-verwijdering is vervallen. |
| `database/migrations/2026_08_16_021801_normalise_existing_customer_phone_numbers.php` | Zet de al opgeslagen nummers eenmalig om, zodat bestaande klanten meteen weer vindbaar zijn. |

**Bewijs.** Nieuwe testklasse `CustomerPhoneNumberTest` met **20 tests**: zeven schrijfwijzen (kaal, met koppelteken, met spaties, met spaties eromheen, Belgisch, Duits, met een `+` in plaats van `00`) worden zowel bij het opslaan als bij het zoeken gecontroleerd, plus het weigeren van te korte en niet-numerieke invoer, plus de controle dat een terugkerende gast die zijn nummer anders schrijft geen tweede klantrecord oplevert.

Daarnaast is `test_a_receptionist_may_look_a_customer_up` in `RoutePermissionsTest` aangepast: die test overschreef het telefoonnummer van de factory en verhulde daarmee de fout. Hij laat het nummer nu aan de factory over. Dat is de directe les uit deze bevinding, vertaald naar code.

---

#### BV-02 — `customers:anonymize` anonimiseert actieve klanten *(Hoog)*

**Betreft:** US-28, `app/Console/Commands/AnonymizeCustomers.php`, HT-08

**Omschrijving.** De opdracht koppelt klanten aan hun reserveringen met een `join` en loopt vervolgens over het resultaat heen. Een `join` levert echter **één rij per reservering** op, niet één rij per klant. Voor elke rij wordt afzonderlijk beoordeeld of die reservering ouder is dan zeven jaar. Een klant met een reservering uit 2018 én een reservering uit 2025 levert dus twee rijen op, waarvan de eerste aan de voorwaarde voldoet — en de klant wordt geanonimiseerd. Dat is precies wat de opmerking in de code wil voorkomen: *"this way we dont remove users who still use our services"*.

Daarnaast telt de teller `$count` het aantal rijen en niet het aantal klanten, waardoor de melding aan het eind een te hoog getal noemt, en wordt `anonymize()` bij meerdere oude reserveringen meerdere keren op dezelfde klant uitgevoerd.

**Impact.** Onomkeerbaar verlies van klantgegevens van gasten die de camping nog steeds bezoeken. De opdracht staat ingepland en draait automatisch, dus dit zou zonder ingrijpen op enig moment in productie gebeuren.

**Reproductie (HT-08).** Maak een klant aan met `created_at` acht jaar geleden en twee reserveringen: één van acht jaar geleden en één van vorige maand. Draai `php artisan customers:anonymize`. Verwacht: de klant blijft ongemoeid. Werkelijk: de klant wordt geanonimiseerd.

**Voorstel voor verbetering.** Per klant de **meest recente** reservering bepalen in plaats van over alle reserveringen te lopen, bijvoorbeeld met een `whereDoesntHave` op reserveringen van de afgelopen zeven jaar, of met een gegroepeerde subquery op `MAX(arrangements.created_at)`. Tegelijk de magische waarde `2556` vervangen door `now()->subYears(7)`, zodat de twee voorwaarden in de opdracht dezelfde grens gebruiken.

**Herstel (ticket 43, uitgevoerd op 16 augustus 2026 door DV).** De `join` is vervangen door een vraag naar de klanten zelf: klanten die langer dan zeven jaar geleden zijn aangemeld, die minstens één reservering hebben, en die géén reservering hebben van ná de peilgrens. Daarmee levert de query één rij per klant en wordt niet langer per reservering geoordeeld. De grens staat nu op één plek als constante `RETENTION_YEARS = 7`, zodat de twee voorwaarden niet meer uit elkaar kunnen lopen, en de teller telt klanten in plaats van rijen. `Carbon::parse()` is niet meer nodig, waarmee ook BV-04 verdwijnt.

**Bewijs.** Nieuwe testklasse `AnonymizeCustomersTest` met tien tests. De test die deze bevinding vasthoudt is `test_a_customer_who_still_books_is_left_alone`: een klant met een reservering van acht jaar geleden én één van vorige maand moet ongemoeid blijven. Verder wordt getest wie wél aan de beurt is, dat een klant zónder reserveringen buiten schot blijft, dat een verblijf van precies zeven jaar geleden de klant nog behoudt, dat de teller klanten telt en niet reserveringen, en dat de opdracht dagelijks ingepland staat. De klok wordt in deze tests stilgezet, anders is de grens van zeven jaar niet exact te testen.

---

#### BV-03 — Geen geautomatiseerde test op de AVG-functionaliteit *(Midden)*

**Betreft:** US-28

**Omschrijving.** Er bestaat geen enkele test op `customers:anonymize`, niet op de opdracht zelf en niet op de inplanning ervan. Van de dertig user stories is dit de enige die volledig ongedekt is. Dat is ongelukkig, want het is tegelijk de meest risicovolle functie in het systeem: hij verwijdert onomkeerbaar gegevens, hij draait automatisch en niemand kijkt mee.

**Conclusie.** Het is geen toeval dat de enige ongeteste functie ook de functie is waarin de zwaarste fout is aangetroffen (BV-02). Deze bevinding is gevonden door de user stories langs de testdekking te leggen, niet door de tests te draaien — het opstellen van de user stories heeft zich daarmee terugbetaald.

**Voorstel voor verbetering.** Een testklasse `AnonymizeCustomersTest` met minimaal vier gevallen: een klant zonder enige reservering, een klant met alleen oude reserveringen (moet geanonimiseerd worden), een klant met een oude én een recente reservering (moet ongemoeid blijven — dit is de test die BV-02 aantoont) en een klant die precies op de grens van zeven jaar zit. Aanvullend een test die controleert dat de opdracht in de planning staat.

**Herstel (ticket 44, uitgevoerd op 16 augustus 2026 door RS).** `AnonymizeCustomersTest` telt tien tests en dekt alle voorgestelde gevallen, aangevuld met het wissen van elk herleidbaar veld, het verwijderen van het gekoppelde account en de tekst die de opdracht rapporteert. US-28 is daarmee niet langer de enige ongedekte user story: alle dertig zijn nu geautomatiseerd afgedekt.

**Wat de dekking direct opleverde.** Het schrijven van deze tests bracht BV-09 aan het licht, een fout die vier weken in de code stond en waardoor de opdracht nooit gedraaid kán hebben. Dat is de zichtbaarste bevestiging van de conclusie in 6.5, punt 4.

---

#### BV-04 — PHPStan-melding op `AnonymizeCustomers` *(Midden)*

**Betreft:** US-28, `AnonymizeCustomers.php:41`

**Omschrijving.** PHPStan meldt op level 5: *toegang tot de niet-bestaande eigenschap `App\Models\Customer::$arrangement_created_at`*. Bij het draaien bestaat die eigenschap wél, omdat de query hem als alias meegeeft (`arrangements.created_at as arrangement_created_at`). Het is dus geen storing bij het draaien, maar wel een reëel risico: zodra iemand de `select` aanpast, levert de eigenschap stilzwijgend `null` op. `Carbon::parse(null)` geeft dan de huidige tijd terug, waardoor de opdracht zonder foutmelding niemand meer anonimiseert — en dat merkt niemand, want er is geen test (BV-03).

**Voorstel voor verbetering.** De eigenschap in het model documenteren met een `@property-read`-annotatie, of — beter, en tegelijk de oplossing voor BV-02 — de join vervangen door een subquery met `addSelect`, zodat het type wel af te leiden is.

**Herstel (ticket 46, uitgevoerd op 16 augustus 2026 door DV).** Zoals verwacht is deze melding vanzelf verdwenen met het herstel van BV-02: zonder join is er ook geen alias meer. De twee gereserveerde uren zijn niet nodig geweest. PHPStan level 5 geeft sinds 16 augustus **nul meldingen** over de hele codebase.

---

#### BV-05 — Twee bestanden voldoen niet aan de codeconventies *(Laag)*

**Betreft:** `AnonymizeCustomers.php` en `Customer.php`

**Omschrijving.** `vendor/bin/pint --test` meldt afwijkingen in twee bestanden. In `AnonymizeCustomers` gaat het om spaties rond de puntoperator; in `Customer` om vijf regels, waaronder de plaatsing van accolades en de ruimte tussen eigenschappen. In opdracht 3 is vastgelegd dat de codebase conform Laravel Pint wordt opgeleverd; die afspraak wordt hier niet nagekomen.

**Voorstel voor verbetering.** `vendor/bin/pint` draaien en het resultaat controleren. Om herhaling te voorkomen wordt de controle opgenomen in het testprotocol (4.3) als voorwaarde voor het pushen naar `master`.

**Herstel (ticket 47, uitgevoerd op 16 augustus 2026).** Beide bestanden zijn tijdens het herstel van BV-01 en BV-02 aangeraakt en zijn daarmee vanzelf langs Pint gegaan, omdat het protocol dat voorschrijft vóór het pushen. `vendor/bin/pint --test` meldt sinds 16 augustus geen afwijkingen meer. Dit is precies het beoogde effect van de opname in het protocol: stijlafwijkingen verdwijnen zodra een bestand toch al open ligt, in plaats van dat ze een eigen taak worden.

---

#### BV-09 — `anonymize()` schrijft naar een kolom die niet bestaat *(Hoog)*

**Betreft:** US-28, `Customer::anonymize()`
**Gevonden op:** 16 augustus 2026, tijdens het schrijven van de tests voor BV-03

**Omschrijving.** De methode die de gegevens overschrijft zet acht velden, waaronder `'zip' => '**'`. De tabel `customers` heeft echter geen kolom `zip` maar `postal_code`; de kolom `zip` komt in geen enkele migratie voor. Omdat het model niets afschermt (`$guarded = []`) wordt die waarde één op één als kolom naar de database gestuurd. Dat heeft twee gevolgen tegelijk:

1. De opdracht **loopt vast** op de eerste klant die hij wil anonimiseren, met een databasefout over een onbekende kolom.
2. De **postcode wordt nooit gewist**. Dat is het veld dat in combinatie met een huisnummer een adres eenduidig herleidbaar maakt, en dus precies het veld dat de AVG-maatregel moet weghalen.

**Waarom dit niet eerder is opgevallen.** De opdracht draait dagelijks, maar tot 16 augustus stond er geen klant in de database die oud genoeg was om aan de voorwaarde te voldoen. De lus liep dus altijd nul keer en de fout is nooit uitgekomen. Zodra de eerste klant de zeven jaar zou passeren, was de anonimisering stilzwijgend blijven hangen — er is geen test (BV-03) en de opdracht meldt niets als hij niets doet.

**Herstel (binnen ticket 43).** `'zip'` is `'postal_code'` geworden. Aanvullend is de verwijdering van het gekoppelde account veilig gemaakt met `?->delete()`, zodat een klant die naar een inmiddels verwijderd account verwijst de hele opdracht niet meer laat vastlopen.

**Bewijs.** `test_every_identifiable_field_is_wiped` controleert alle zeven velden apart, inclusief `postal_code`. `test_a_customer_who_still_books_is_left_alone` controleert de postcode nogmaals, nu vanuit de omgekeerde kant: bij een klant die ongemoeid moet blijven, moet die postcode er juist nog staan.

**Conclusie.** Deze bevinding is niet gevonden door de software te gebruiken en ook niet door de tests te draaien, maar door een test te *schrijven* voor een functie die er nog geen had. Drie van de vier bevindingen op US-28 (BV-02, BV-04 en BV-09) zijn op die manier boven water gekomen.

---

#### BV-06 — Regressie door omzetten van e-mail naar de wachtrij *(Hoog — gesloten)*

**Betreft:** US-09, US-12, US-17

**Gevonden op:** dinsdag 11 augustus 2026, tijdens taak T-05
**Hersteld op:** dinsdag 11 augustus 2026, dezelfde dag
**Hersteld door:** Ricardo Sas (RS)

**Omschrijving.** Om te voorkomen dat een bezoeker na het bevestigen van zijn boeking op een trage mailserver moet wachten, is aan alle zes de Mailables `implements ShouldQueue` toegevoegd. Direct daarna sloegen zeven bestaande tests rood:

| Testklasse | Rode tests |
|---|---|
| `Auth\PasswordResetTest` | 3 |
| `BookingMailSentTest` | 2 |
| `NewAccountMailSentTest` | 1 |
| `PaymentPageTest` | 1 |

**Analyse.** De oorzaak was niet dat het systeem stukging, maar dat de aanname in de tests niet meer klopte. `Mail::assertSent()` controleert of een bericht *direct is verstuurd*. Een bericht dat `ShouldQueue` implementeert wordt echter niet verstuurd maar *in de wachtrij gezet*, en daarvoor bestaat een andere controle: `Mail::assertQueued()` respectievelijk `Notification::assertQueuedTo()`. Handmatig doorklikken zou dit verschil nooit hebben laten zien — de mail kwam immers gewoon aan — terwijl de verandering wel degelijk fundamenteel is.

**Herstel.** Zeven assertions in vier testbestanden omgezet van `assertSent`/`assertSentTo` naar `assertQueued`/`assertQueuedTo`. De gedragswijziging is gecontroleerd vóór het aanpassen van de tests: er is met een draaiende wachtrij-worker vastgesteld dat de e-mail nog steeds daadwerkelijk aankomt (HT-01). Pas daarna is vastgesteld dat de verwachting in de tests fout was en niet de code.

**Conclusie.** Dit is het beste bewijs dat de testsuite doet waarvoor hij is opgezet. Een wijziging die er onschuldig uitzag en de gebruiker geen zichtbaar verschil opleverde, raakte drie user stories tegelijk in vier verschillende delen van het systeem. De suite maakte dat binnen elf seconden zichtbaar. Uit deze bevinding is de regel in 4.3 voortgekomen: een test wordt nooit aangepast om hem groen te krijgen zonder onderzoek naar de oorzaak.

---

#### BV-07 — Voorbeeldtests uit de standaardinstallatie *(Laag)*

**Betreft:** `tests/Unit/ExampleTest.php`, `tests/Feature/ExampleTest.php`

**Omschrijving.** Twee van de 173 tests zijn overgebleven uit de standaardinstallatie van Laravel. `test_that_true_is_true` bewijst niets over deze applicatie en maakt het aantal tests optisch groter dan de werkelijke dekking. Dat is misleidend in de rapportage.

**Voorstel voor verbetering.** `tests/Unit/ExampleTest.php` verwijderen; `tests/Feature/ExampleTest.php` (die de homepage opvraagt) hernoemen naar een test die aansluit bij US-01 en opnemen in `HomepageLocationsTest`.

---

#### BV-08 — Restrisico: het wachtrij-pad wordt niet end-to-end bewezen *(Laag, bewust niet ingepland)*

**Omschrijving.** Sinds BV-06 verlaat alle uitgaande post het systeem via de wachtrij. In de testomgeving staat `QUEUE_CONNECTION=sync` en vangt `Mail::fake()` het bericht af vóórdat de wachtrij eraan te pas komt. Geen enkele geautomatiseerde test bewijst dus dat het volledige pad werkt met `QUEUE_CONNECTION=database`, de instelling die in `.env` staat. Draait er geen `queue:work`, dan verstuurt het systeem geen enkele e-mail en geeft het nergens een foutmelding.

**Afweging.** Het risico is bekend en in de README beschreven; het opstarten van de wachtrij staat in de installatie-instructie. Een geautomatiseerde bewaking hierop (bijvoorbeeld een controle op de leeftijd van de oudste taak in de wachtrij) is productieluxe die niet past bij de omvang van dit project. In overleg met de opdrachtgever is besloten dit **niet** in te plannen en het risico te aanvaarden; het is wel opgenomen in het opleverdocument als beheerinstructie. Zie 7.3.

### 6.5 Conclusies

1. **De basis staat.** 173 geautomatiseerde tests met ruim 1.400 assertions dekken 29 van de 30 user stories af en draaien in 10,5 seconde. Daarmee is voldaan aan de aanleiding voor deze opdracht: elke ontwikkelaar kan vóór elke commit binnen enkele seconden vaststellen of hij iets heeft gebroken.
2. **Rechten zijn het best afgedekt.** De 29 tests in `RoutePermissionsTest` en `PermissionMiddlewareTest` controleren per route en per rol wie er wel en niet doorheen komt, inclusief gebruikers zonder rol en niet-ingelogde bezoekers. Dat is verantwoord: het uitlekken van klantgegevens is het grootste risico van dit systeem.
3. **Groen is geen bewijs van werkende software.** Alle tests slaagden, en toch zijn er twee fouten met hoge prioriteit gevonden. BV-01 kwam aan het licht doordat de user story naast de test werd gelegd en bleek dat de test zijn eigen testdata had bijgesteld tot de code slaagde. Dit is de belangrijkste inhoudelijke conclusie: een test is alleen waardevol als de gebruikte gegevens overeenkomen met de werkelijkheid.
4. **De ongeteste hoek is de gevaarlijkste gebleken.** De enige user story zonder dekking (US-28, anonimiseren) bevat de zwaarste fout (BV-02), de enige PHPStan-melding (BV-04) en de helft van de stijlafwijkingen (BV-05) — en toen er alsnog een test voor werd geschreven, kwam daar meteen een vierde fout uit die de functie volledig onbruikbaar maakte (BV-09). Vier van de negen bevindingen komen uit één ongeteste functie van dertig regels. Ontbrekende dekking is daarmee geen administratief probleem maar de beste voorspeller van fouten die dit rapport heeft opgeleverd.
5. **De regressiebewaking heeft zich al bewezen.** BV-06 laat zien dat één regel wijziging per Mailable zeven tests in vier bestanden raakte, zonder dat een gebruiker er iets van zou merken. Zonder suite was deze wijziging zonder verder onderzoek doorgevoerd.
6. **De handmatige tests blijven nodig, maar zijn beperkt van omvang.** Van de negen handmatige testgevallen zijn er zeven akkoord, één met een aanvaard aandachtspunt (HT-07, horizontaal schuiven op een telefoon) en één afgekeurd (HT-08). Automatisering van de overige acht zou meer onderhoud kosten dan het per sprint doorlopen ervan.

### 6.6 Hertest na het herstel

De twee bevindingen met hoge prioriteit zijn naar voren gehaald en op 16 augustus hersteld, vooruitlopend op de herstelweek. Daarbij zijn BV-03, BV-04, BV-05 en de nieuwe BV-09 meegelopen. Na het herstel is de volledige suite opnieuw gedraaid.

| | Testronde 13-08-2026 | Hertest 16-08-2026 |
|---|---|---|
| Uitgevoerde tests | 173 | **213** |
| Assertions | circa 1.400 | **circa 1.490** |
| Gefaald | 0 | **0** |
| Doorlooptijd | 10,49 s | 14,39 s |
| PHPStan level 5 | 1 melding | **0 meldingen** |
| Laravel Pint | 2 bestanden afwijkend | **0 bestanden afwijkend** |
| User stories zonder dekking | 1 (US-28) | **0** |

Het aantal assertions staat hier bij benadering. Een deel van de tests loopt over gegenereerde gegevens uit de seeders, waardoor het aantal controles per ronde een paar tientallen verschilt terwijl het aantal tests gelijk blijft. Het testaantal is daarom de maat die in dit rapport telt; het aantal assertions geeft alleen de orde van grootte aan.

De veertig nieuwe tests zitten in `CustomerPhoneNumberTest` (24), `AnonymizeCustomersTest` (10), `PaymentPageTest` (4), `DashboardCalendarTest` (1) en `RoutePermissionsTest` (1). De laatste vijf horen niet bij een bevinding uit dit rapport maar bij drie tickets uit opdracht 3 die nog openstonden en tegelijk zijn afgemaakt: 9b (betaalwijze en moment van ontvangst vastleggen bij een boeking), 23b (de dubbele API-route verwijderen) en 12b (het demo-account koppelen in de seeder). Er zijn geen bestaande tests rood geworden. Dat is belangrijker dan het aantal: de normalisatie van het telefoonnummer is naar het model verplaatst en raakt daarmee de boekingsflow, de accountaanmaak, de seeders en het baliescherm tegelijk. De suite laat zien dat geen van die vier iets van de verplaatsing merkt. De regressiebewaking heeft hier dus voor de tweede keer haar nut bewezen — nu niet bij het ontstaan van een fout, maar bij het herstellen ervan.

**Meegenomen buiten de bevindingen om.** Doordat het telefoonnummer voortaan als kale cijfers wordt opgeslagen, toonden de schermen `0624815903` in plaats van `06-24815903`. Dat is opgelost met een afgeleide eigenschap op het model (`phone_number_formatted`) die de leesbare notatie samenstelt. Die keuze is bewust: hij staat op de server en is daarmee met PHPUnit te testen, terwijl dezelfde logica in een Vue-component buiten elke geautomatiseerde test zou vallen (zie 2.3). Alleen een Nederlands mobiel nummer en een internationaal nummer worden opgemaakt; bij vaste nummers verschilt de lengte van het netnummer en zou opmaken het juist minder leesbaar maken.

Alle bevindingen met prioriteit hoog of midden zijn hiermee gesloten. Open blijven BV-07 (opruimen van de voorbeeldtests, laag) en BV-08 (restrisico wachtrij, bewust aanvaard).

---

## 7. Bewijsstuk 5 — Teamplanning herstel fouten

### 7.1 Herstelplanning

De bevindingen zijn geprioriteerd op impact voor de gebruiker en op risico voor de gegevens. BV-02 staat bovenaan: die fout verwijdert onomkeerbaar klantgegevens en de opdracht draait automatisch, dus elke dag uitstel is een dag risico. BV-01 volgt direct daarna, omdat de balie er dagelijks tegenaan loopt.

De herstelwerkzaamheden zijn als nieuwe tickets aan de backlog uit opdracht 1 en 3 toegevoegd; die liep tot en met ticket 42, dus de nieuwe tickets beginnen bij 43.

| Ticket | ID | Wat wordt hersteld | Prioriteit | Schatting | Wie | Wanneer |
|---|---|---|---|---|---|---|
| 43 | BV-02, BV-09 | Query in `customers:anonymize` omzetten zodat per klant de meest recente reservering telt; teller herstellen; magische waarde vervangen; `zip` corrigeren naar `postal_code` | Hoog | 5 uur | DV | **gereed 16 aug** |
| 44 | BV-03 | Testklasse `AnonymizeCustomersTest` met vier scenario's, waaronder het scenario dat BV-02 aantoont | Midden | 4 uur | RS | **gereed 16 aug** |
| 45 | BV-01 | Telefoonnummer normaliseren bij opslaan, validatie op drie plaatsen gelijktrekken, bestaande gegevens omzetten, test aanpassen | Hoog | 3 uur | DV | **gereed 16 aug** |
| 46 | BV-04 | Alias typeerbaar maken (valt grotendeels samen met BV-02); PHPStan opnieuw draaien tot nul meldingen | Midden | 2 uur | DV | **gereed 16 aug**, meegelopen met 43 |
| 47 | BV-05 | `vendor/bin/pint` draaien en het resultaat nalopen; controle opnemen in het testprotocol | Laag | 0,5 uur | RS | **gereed 16 aug**, meegelopen met 43 en 45 |
| 48 | BV-07 | Voorbeeldtests verwijderen en de bruikbare test opnemen in `HomepageLocationsTest` | Laag | 1 uur | RS | wo 19 aug |
| 49 | — | Volledige regressieronde en handmatig protocol opnieuw doorlopen als opleverkeuring | — | 1 uur | MV | do 20 aug |
| 50 | — | Leesbare weergave van het telefoonnummer op de schermen, als gevolg van ticket 45 | Laag | 1 uur | DV | **gereed 16 aug** |
| — | BV-08 | Bewust niet ingepland, risico aanvaard (zie 7.3) | Laag | 0 | — | — |
| | | **Totaal** | | **17,5 uur** | | |

**Naar voren gehaald.** De tickets 43 tot en met 47 en 50 zijn op 16 augustus al uitgevoerd, in overleg met de opdrachtgever (besluiten 1 tot en met 3 in 7.3). Voor BV-02 gold dat de opdracht tot het herstel uit de planning was gehaald en de camping in die tussentijd niet aan de bewaartermijn voldeed; voor BV-01 dat de balie met een papieren lijst werkte. Geen van beide kon nog een week wachten. De hertest staat in 6.6.

Van de zestien geplande uren zijn er vier niet nodig geweest: ticket 46 loste zichzelf op met het herstel van BV-02, en ticket 47 liep mee met twee bestanden die toch al open lagen. Daar staat één nieuwe fout tegenover (BV-09), die binnen ticket 43 is meegenomen, plus het nieuwe ticket 50 van één uur. Per saldo is de herstelweek daarmee ruim een dag korter dan begroot. In de herstelweek resteren nog de tickets 48 en 49.

**Volgorde en afhankelijkheden.** BV-03 wordt bewust ná BV-02 ingepland maar door een andere ontwikkelaar geschreven, zodat degene die de test schrijft niet degene is die de oplossing heeft bedacht. BV-04 wordt na BV-02 opgepakt omdat de voorgestelde oplossing voor BV-02 (subquery in plaats van join) de PHPStan-melding waarschijnlijk vanzelf oplost; de twee uur is gereserveerd voor het geval dat niet zo blijkt te zijn. BV-01 gaat naar DV omdat de wijziging aan het gegevensmodel raakt en hij de omzetting van de bestaande gegevens uitvoert.

**Belasting per teamlid in de herstelweek:** DV 10 uur, RS 5,5 uur, MV 1 uur. MV heeft die week ruimte voor de oplevering en de overdracht.

### 7.2 Verwerking in de tijdlijn

| Datum | Activiteit |
|---|---|
| za 16 augustus | Tickets 43 t/m 47 en 50 uitgevoerd, naar voren gehaald (BV-01 t/m BV-05 en BV-09) |
| ma 17 – wo 19 augustus | Ticket 48 (BV-07); de vrijgevallen tijd gaat naar het bijwerken van dit rapport en de overdracht |
| do 20 augustus | Ticket 49: volledige regressieronde + handmatig testprotocol; dit geldt als opleverkeuring |
| vr 21 augustus | Oplevering aan de opdrachtgever |

De oplevering schuift naar 21 augustus. Van die verschuiving komt één week voor rekening van de testfase, die in de oorspronkelijke planning helemaal niet als eigen fase was opgenomen, en vier dagen voor rekening van het herstel. Die vier dagen vallen binnen de herstelweek die al was gereserveerd, en kosten dus geen extra doorlooptijd.

### 7.3 Afstemming met de opdrachtgever

**Overleg:** donderdag 13 augustus 2026, aanwezig: de eigenaar van Syntec Camping en Marco Verbist (MV) namens het ontwikkelteam.

Aan de opdrachtgever zijn de testresultaten en de acht bevindingen voorgelegd, met per bevinding de impact op de dagelijkse gang van zaken op de camping in plaats van in technische termen. Vervolgens is per bevinding besproken of, wanneer en door wie die wordt hersteld.

| Nr | Besluit | Onderbouwing |
|---|---|---|
| 1 | BV-02 krijgt de hoogste prioriteit en wordt als eerste hersteld | De opdrachtgever gaf aan dat vaste gasten die om het jaar terugkomen geen uitzondering zijn maar juist de kern van zijn klantenbestand. Het verlies van hun gegevens weegt voor hem zwaarder dan alle andere bevindingen samen. |
| 2 | De opdracht `customers:anonymize` wordt tot het herstel uit de planning gehaald | Op verzoek van de opdrachtgever: liever tijdelijk niet aan de bewaartermijn voldoen dan onomkeerbaar de verkeerde gegevens wissen. Vastgelegd als tijdelijke maatregel met een einddatum van 19 augustus. |
| 3 | BV-01 wordt in dezelfde week hersteld | De baliemedewerkers werken nu met een papieren lijst omdat het zoeken niet werkt. |
| 4 | BV-08 (bewaking op de wachtrij) wordt niet ingepland | De opdrachtgever herkent het risico maar vindt de bewaking te zwaar voor een camping van deze omvang. Afgesproken is dat het opstarten van de wachtrij in de beheerinstructie komt te staan en dat de camping bij het uitblijven van boekingsbevestigingen als eerste daarop controleert. |
| 5 | HT-07 (horizontaal schuiven van het reserveringsoverzicht op een telefoon) wordt geaccepteerd | De balie werkt op een vaste computer; een mobiele weergave van het beheerscherm is geen wens. |
| 6 | De oplevering verschuift naar 21 augustus | De opdrachtgever gaat akkoord, onder de voorwaarde dat de publieke website ongewijzigd in de lucht blijft. Daaraan wordt voldaan: geen van de bevindingen raakt de publieke pagina's. |
| 7 | Het testprotocol wordt vanaf nu bij elke oplevering doorlopen | Op eigen initiatief van de opdrachtgever, na de uitleg over BV-06: hij wil een aantoonbare controle vóór elke oplevering. |

Het verslag van dit overleg is per e-mail bevestigd aan de opdrachtgever en door hem akkoord bevonden.

---

## 8. Conclusie en advies

De testactiviteiten zijn uitgevoerd volgens een vooraf vastgestelde aanpak: dertig user stories, een geautomatiseerde suite van 173 tests, statische analyse en negen handmatige testgevallen. Alle geautomatiseerde tests slagen, en toch zijn er acht bevindingen opgetekend waarvan twee met hoge prioriteit. Die combinatie is de kern van de conclusie: **een groene testsuite bewijst dat de aannames in de tests kloppen, niet dat de software doet wat de gebruiker wil.** Alleen door de user stories systematisch langs de dekking te leggen zijn BV-01 en BV-03 aan het licht gekomen.

Adviezen voor het vervolg:

1. **Neem testen op als eigen taak in elke planning.** In opdracht 1 zat testen verstopt in de bouwuren; de tweeënvijftig uur uit hoofdstuk 5 waren daardoor onzichtbaar. Reken voortaan per user story ongeveer een derde van de bouwtijd voor het testen.
2. **Schrijf de test vanuit de gegevens die er echt zijn.** BV-01 is ontstaan doordat een test zijn eigen gegevens naar de code toe schreef. Laat een test bij voorkeur de factory of de seeder gebruiken zonder de waarden te overschrijven die het onderwerp van de test zijn.
3. **Geen functie in productie zonder test, zeker niet als hij automatisch draait en gegevens verwijdert.** US-28 was de enige ongedekte story en leverde drie bevindingen op.
4. **Houd het protocol kort genoeg om het echt te doen.** De suite draait in tien seconden en de handmatige ronde kost twee uur per sprint. Dat is de reden dat het protocol ook daadwerkelijk gevolgd zal worden; een browser-testsuite van een half uur zou dat niet zijn.
