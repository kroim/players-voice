=== WP Downgrade | Specific Core Version ===
Contributors: Reisetiger
Donate link: http://www.reisetiger.net
Tags: Downgrade, Core, WP-Core, Version, Rollback, Upgrade, Update, Release, Versionskontrolle
Requires at least: 3.0.1
Tested up to: 4.9
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically downgrad or update to any WordPress version you want directly from the backend.

== Description ==

= WordPress Core Downgrade/Update =
**EN:** The plugin "WP Downgrade" forces the WordPress update routine to perform the installation of a **specified** WordPress release. The Core Release you specify is then downloaded from wordpress.org and installed as would **any regular update**. You can permanently stay on a previous version of your choice or update selected. 

The user Gahapati describes it so much better than I can. (Thank you!)
> *WP Downgrade | Specific Core Version* has the potential for becoming one of the best-loved plugins among those, who simply cannot update to the *latest* WP release.  

> In the past the latest WP release was the only offering for WP's Automatic Update routine. This left all those behind, who have to wait with Core updates, until their plugins become compatible with newer WP releases. When this finally happens, more often than not there has already been *yet another* Core update. In the end a dreaded, cumbersome, time-consuming and error-prone Manual Update used to be the only way to go.  

> With *WP Downgrade | Specific Core Version* this is now a thing of the past. Anyone who lags behind the latest WP release is now able to use Automatic Updates even to lower WP versions. What WP Downgrade does simply is to make WP believe that the version you want to update to *actually is* the latest version. Because of this, there is no difference to updating to the latest version.  

> For security reasons I think this is a must-have plugin for anyone running a "seasoned" WP installation, and it actually should be a Core feature to be able to update not to the *latest* WP release exclusively but instead to have a choice among *secure* releases.

**DE:** Das Plugin "WP Downgrade" zwingt die WordPress-interne Update-Funktion, ein **bestimmtes** WordPress-Release zu installieren. Das definierte Core-Release wird **wie ein regul&auml;res Update** von wordpress.org bezogen und direkt installiert. 
Das klappt wie gewohnt per Update-Button im Admin-Bereich oder per Auto-Update. Die Versionsnummer kann sowohl **höher** als auch **niedriger** sein als die aktuell installierte Version. Somit ist also auch ein Rollback auf frühere Releases möglich. Dein WordPress wird solange auf dieser Version bleiben, bis du eine neue Versionsnummer in WP-Downgrade hinterlegst (oder bis du die Versionsnummer leerst oder das Plugin deaktivierst).

= Achtung: Nutzung auf eigene Gefahr! =
WP-Downgrade funktioniert normalerweise prima. Trotzdem ist ein Versionswechsel immer ein riskanter Eingriff! Du solltest auf jeden Fall vorher ein Backup deiner Dateien und der Datenbank anlegen! Ich übernehme keinerlei Gewähr für deine Installation und werde auch keinen Support leisten.

= Plugin hilfreich? Sag Danke! =
Ich stelle das Plugin kostenlos zur Verf&uuml;gung und m&ouml;chte auch keine Spenden. Aber: Ich freu mich sehr, wenn du auf meinen Reiseblog [https://www.reisetiger.net](https://www.reisetiger.net "Reisetiger") verlinkst oder mal für deine Reiseplanung vorbeischaust! :-)
**Bitte bewerte WP Downgrade, wenn es f&uuml;r dich n&uuml;tzlich ist!**

== Installation ==
= Der einfachste Weg: =
1. Gehe in deinem Wordpress Backend auf Plugins -> Installieren und suche dort nach "WP Downgrade". 
2. Klicke in der Trefferliste bei "WP Downgrade" auf "Jetzt installieren"
3. Aktiviere das Plugin
4. Nun findest du unter "Einstellungen" einen neuen Punkt namens "WP Downgrade". Dort kannst du die gewünschte Core-Versionsnummer hinterlegen und anschließend das WordPress-Update vornehmen. 

= Der manuelle Weg: =
1. Lade das Plugin herunter 
2. Entpacke die ZIP-Datei 
3. Lade den gesamten Ordner `wp-downgrade` per FTP in das Verzeichnis `/wp-content/plugins/` auf deinen Blog hoch
4. Gehe in deinem Wordpress Backend zu Plugins und aktiviere das Plugin
5. Nun findest du unter "Einstellungen" einen neuen Punkt namens "WP Downgrade". Dort kannst du die gewünschte Core-Versionsnummer hinterlegen und anschließend das WordPress-Update vornehmen. 


== Frequently Asked Questions ==

= Q: Warum gibt es hier keine FAQ's? =
A: Weil es bisher keine Fragen gab! :-)


== Screenshots ==

1. Release-Nummer hinterlegen
2. Downgrade auf hinterlegte WordPress-Version aktivieren
3. WordPress bietet eine ältere Version als Update an


== Changelog ==
= 1.1.4 =
* Improved access to the settings page (link from plugin overview)
* compatibility with WordPress 4.7.1

= 1.1.3 =
* bugfix on downloading certain languages
* compatibility with WordPress 4.7
 
= 1.1.2 =
* cleaned up code
* small design change
* added version number check

= 1.1.1 =
* Englische Sprachdateien hinzugef&uuml;gt. Vielen Dank an Gahapati!!

= 1.1.0 =
* Fehler bei Sprachen ungleich "de_DE" behoben. Danke an Gahapati!!

= 1.0.0 =
* Erste stabile Beta-Version
* WordPress Downgrade oder Update nach Wahl
* Funktioniert für Deutsche Sprachversion

== Upgrade Notice ==
