# Changelog

Alle wichtigen Änderungen an der Tommy Edition werden hier dokumentiert.

Das Projekt orientiert sich an Keep a Changelog und Semantic Versioning.

---

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