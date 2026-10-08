# Changelog

Alle wichtigen Änderungen an der Tommy Edition werden hier dokumentiert.

Das Projekt orientiert sich an Keep a Changelog und Semantic Versioning.

---

## [v1.1.0] — 2026-10-08 — Einstellungen und Feiertage

### Added
- Geschützte Einstellungen für Standardansicht, alle 16 Bundesländer und Feiertagsjahre pro Shield-Benutzer und aktivem Mandanten; neue Migration mit eindeutigem Benutzer-/Mandantenschlüssel.
- Dynamische Vorgaben: Monat, Niedersachsen und aktuelles Jahr plus Folgejahr; Validierung und sofortige Anwendung nach Speichern.
- Geschützter Feiertagsfeed mit serverseitigem CodeIgniter-HTTP-Client für api-feiertage.de, Prüfung der Anbieterantwort und Bundeslandzugehörigkeit.
- Schreibgeschützte, optisch abgesetzte Ganztagsfeiertage in Monat/Woche/Tag, unabhängig von eigenen Kalenderfiltern.
- Gemeinsamer Cache je Bundesland/Jahr: 24 Stunden frisch, 90 Tage Rückfall, 15 Minuten Wiederholsperre bei Fehlern/fehlenden Jahren und rollierendes Budget von 90 Aufrufen pro Stunde mit lokaler Dateisperre.
- Verständliche Hinweise bei fehlenden Jahrgängen und veralteten Daten; eigene Termine bleiben unabhängig nutzbar.

### Changed
- Navigation einschließlich Einstellungen ist auf dem Handy auch außerhalb des Kalenders erreichbar.
- Lange Feiertagsnamen werden in schmalen Kalenderzellen umbrochen; Feiertage öffnen keinen Editor und bieten keine Änderungsaktionen.
- Kalender, Einstellungen und Feiertagsfeed erhalten `private, no-store`; der bestehende Service Worker bleibt auf statische Assets begrenzt.

### Validation
- PHP-Suite: 22 Tests / 102 Assertions erfolgreich, mit gefälschtem API-Client, echter Shield-Anmeldung/Abmeldung und isolierter SQLite-Testdatenbank; bestehende und neue Terminoperationstests eingeschlossen.
- `npm run build` erfolgreich.
- Settings und alle drei Kalenderansichten visuell auf 390 × 844 und 820 × 1180 Pixeln mit gerenderten Testseiten/echten Assets geprüft; Filterunabhängigkeit und Feiertagsklick zusätzlich im Browser geprüft.
- Anbieterjahre sind nicht garantiert; lokale Cache-/Aufrufkoordination gilt je Installation. Physische Mobilgeräte und produktiver Mehrserverbetrieb wurden nicht geprüft. Details in README.

## [v1.0.1] — 2026-09-29 — Dashboard Hotfix

### Fixed
- Die gemeinsame Sidebar kann nach Login auch ohne Kalenderkontext auf dem Dashboard gerendert werden.
- Kalenderfilter und deren Aktionen werden nur auf der Kalenderseite ausgegeben.

## [v1.0.0] — 2026-09-29 — Stand-alone Calendar PWA

### Added
- Kalenderauswahl für neue und bestehende Termine.
- Mandantensichere Tests für Kalenderauswahl und Termin-Feed.
- Sofort wirksame Kalenderfilter mit mandantenspezifischer Speicherung im Browser.
- Installierbare Online-first-PWA mit Manifest, App-Icon, Service Worker und Offline-Hinweis.

### Changed
- Termin-API liefert `calendar_id` und übernimmt Farben ausschließlich aus dem zugeordneten Kalender.
- Erstellung und Änderung weisen Kalender fremder Mandanten serverseitig ab.
- Neu angelegte Kalender erscheinen ohne Reload und sind standardmäßig sichtbar.
- Seitenleiste, Modale, Toolbar und Bedienelemente reagieren auf Handy- und Tabletbreiten.

### Security
- Der Service Worker speichert weder Termin-API-Antworten noch geschützte HTML-Seiten im Cache.

## v0.9.1 — Calendar Management

### Added
- Neues Modal „Kalender anlegen“.
- API `POST /api/calendars`.
- Kalender können einem Mandanten hinzugefügt werden.
- Sidebar zeigt mehrere Kalender dynamisch an.

### Changed
- Sidebar rendert Kalender aus `CalendarModel::forTenant()`.
- 
## v0.9.0 — Foundation

### Refactored
- `calendar.js` vollständig als Tommy Edition Reference File neu strukturiert.
- Klare Kapitelstruktur (Imports, UI, Utilities, API, Calendar, CRUD, Drag & Drop, Initialisierung).
- Tote Debug- und Overlay-Reste entfernt.
- Datums- und Zeit-Helfer zentralisiert.
- Konfiguration (`CONFIG`) eingeführt.

### Changed
- Keine Änderung der Benutzeroberfläche.
- Keine Änderung der Kalenderfunktionalität.
- Grundlage für zukünftige Features geschaffen.

## [v0.8.3] – 2026-09-22 — Event Polish

### Added
- Floating Action Button mit Bootstrap Icons.
- Bootstrap Toast-System mit Success-, Info- und Error-Meldungen.
- Loading Overlay beim Speichern von Terminen.
- Hover-Effekt für Termine.
- Neue Toolbar-Optik im Tommy-Edition-Stil.
- Heute-Markierung mit blauem Kreis.

### Changed
- Event-Layout in Monats-, Wochen- und Tagesansicht überarbeitet.
- Uhrzeit und Titel werden besser dargestellt.
- FullCalendar-Buttons optisch modernisiert.
- Monatsansicht zeigt Uhrzeiten sauber formatiert.

### Fixed
- Browser-Alerts vollständig durch Toasts ersetzt.
- Stabileres `api.save()` mit besserem Fehlerhandling.
- Fokusproblem beim Wechsel zwischen Ganztägig und Uhrzeit behoben.

---

## [v0.8.2] – 2026-09-21 — Feels Like Google Calendar

### Added
- Floating Action Button zum Erstellen neuer Termine.
- Bootstrap Icons über Vite eingebunden.
- Erste Version des Toast-Systems.

### Changed
- Modal kann direkt über den FAB geöffnet werden.

## v0.8.4 — Calendar Colors (Work in Progress)

### Added
- Neues `CalendarModel` für die Tabelle `calendars`.
- Tenant-Helfer `forTenant()` und `defaultCalendar()`.

### Changed
- `EventModel::calendarEvents()` liefert jetzt Kalendername und Kalenderfarbe.
- FullCalendar erhält `backgroundColor`, `borderColor` und `textColor` direkt aus der API.

### Internal
- Vorbereitung für mehrere Kalender mit individuellen Farben.
