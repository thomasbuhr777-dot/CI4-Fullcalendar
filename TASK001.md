# Task: CI4-Fullcalendar als eigenständige Kalender-PWA fertigstellen

Arbeite im Repository `thomasbuhr777-dot/CI4-Fullcalendar` auf Basis des aktuellen Codes. Ziel ist eine eigenständig betreibbare, auf Handy und Tablet gut bedienbare Kalender-PWA. Die spätere Integration in hv3.io ist ein Folgeprojekt; baue jetzt keine HV3-spezifischen Tabellen oder Abhängigkeiten ein.

Beachte `PROJECT.md`: vollständige Dateien, Fat Model / Thin Controller, Vite für Frontend-Assets, Bootstrap 5 und Bootstrap Icons. Gliedere die Arbeit in nachvollziehbare Stories mit jeweils geprüften Commits und dokumentiere Änderungen im `CHANGELOG.md`.

## 1. Kalender und Termine korrekt verbinden

- Ergänze im Formular für neue und bestehende Termine eine Kalenderauswahl mit Name und Farbe.
- Speichere die gewählte `calendar_id`; beim Bearbeiten muss die bisherige Auswahl angezeigt und änderbar sein.
- Ein Termin darf nur einem Kalender des aktiven Mandanten zugewiesen werden. Prüfe das serverseitig bei Erstellung und Änderung.
- Liefere `calendar_id` in der Termin-API mit aus. Die Terminfarbe muss vom zugeordneten Kalender kommen.
- Bestehende Termine und der bisherige Hauptkalender müssen weiterhin funktionieren. Eine notwendige Schemaänderung erfolgt per neuer Migration, ohne alte Migrationen nachträglich zu ändern.

## 2. Filter „Meine Kalender“ fertigstellen

- Die Checkboxen in der Seitenleiste sollen Termine der jeweiligen Kalender unmittelbar ein- und ausblenden, ohne die Seite neu zu laden.
- Die Auswahl soll beim Wechsel zwischen Monat, Woche und Tag sowie nach einem erneuten Laden der Seite erhalten bleiben. Eine Speicherung im Browser genügt für diese Version.
- Neue Kalender sollen nach dem Anlegen in der Liste erscheinen und standardmäßig sichtbar sein.
- Wenn alle Kalender abgewählt sind, bleibt die Kalenderansicht leer und bedienbar.
- Prüfe die Mandantengrenze auch in der Abfrage: Kalender eines anderen Mandanten dürfen weder ausgewählt noch deren Termine angezeigt werden.

Für diese Stand-alone-Version können Kalender innerhalb eines Mandanten gemeinsam sichtbar bleiben. Benutzerprivate Kalender und HV3-spezifische Berechtigungen gehören noch nicht zu diesem Task.

## 3. Mobile Bedienung und PWA

- Gestalte Seitenleiste, Kalenderansichten, Modale und Bedienelemente für schmale Handybildschirme und Tablets. Die Kalenderfilter müssen mobil leicht erreichbar sein.
- Ergänze Manifest, passende Icons und einen Service Worker, sodass die Anwendung über den Browser installiert werden kann.
- Setze die PWA zunächst als **online-first** um: Die Oberfläche darf bei fehlender Verbindung einen verständlichen Offline-Hinweis zeigen. Terminänderungen ohne Verbindung dürfen nicht scheinbar erfolgreich gespeichert werden.
- Cache keine personenbezogenen Termin-API-Antworten dauerhaft im Service Worker. Achte darauf, dass nach Logout kein zuvor gecachter geschützter Inhalt als angemeldete Oberfläche erscheint.
- Die Installation und der Service Worker müssen im vorgesehenen HTTPS-Betrieb funktionieren; dokumentiere die lokalen Testbedingungen.

## 4. Qualität und Abnahme

Prüfe insbesondere:

1. Zwei Kalender mit unterschiedlichen Farben anlegen und je einen Termin zuordnen.
2. Jeden Kalender einzeln aus- und einblenden; Darstellung und Farben nach Seitenwechsel und Reload prüfen.
3. Termin zwischen eigenen Kalendern verschieben und anschließend bearbeiten.
4. Manipulierte `calendar_id` eines fremden Mandanten bei Erstellung und Änderung abweisen.
5. Erstellung, Bearbeitung, Löschen, Drag & Drop und Resize bestehender Termine auf Regressionen prüfen.
6. Installation und Bedienung auf einer schmalen Handyansicht und auf Tabletbreite prüfen; Offline-Hinweis und Wiederverbindung testen.

Führe vorhandene Tests und den Vite-Build aus. Ergänze gezielte Tests für die Mandantenprüfung und die Terminzuordnung. Dokumentiere Start, Konfiguration, Migrationen, Build, PWA-Installation und bekannte Grenzen in einer projektspezifischen `README.md`.

Berichte zum Abschluss die geänderten Dateien, Commits, ausgeführten Prüfungen und verbleibenden Einschränkungen. Ändere oder deploye hv3.io im Rahmen dieses Tasks nicht.