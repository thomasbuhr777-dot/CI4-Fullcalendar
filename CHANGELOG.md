# Changelog

Alle wichtigen Änderungen an der Tommy Edition werden hier dokumentiert.

Das Projekt orientiert sich an Keep a Changelog und Semantic Versioning.

---

## [Unreleased]

### Added
- Kalenderauswahl für neue und bestehende Termine.
- Mandantensichere Tests für Kalenderauswahl und Termin-Feed.

### Changed
- Termin-API liefert `calendar_id` und übernimmt Farben ausschließlich aus dem zugeordneten Kalender.
- Erstellung und Änderung weisen Kalender fremder Mandanten serverseitig ab.

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
