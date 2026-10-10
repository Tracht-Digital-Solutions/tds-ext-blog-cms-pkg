<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Tds\Ext\BlogCms\Support\PostSeeder;

/**
 * Blog-CMS — seed the "passkeys and security keys" guide (DE + EN).
 *
 * Product lines `{{produkt:<slug>}}` use the shop slug of the row's language;
 * see `20260728000012_blog_cms_seed_post_office_wifi.php`.
 */
final class BlogCmsSeedPostSecurityKeys extends AbstractMigration
{
    public const POSTS = [
        [
            'slug' => 'passkeys-und-sicherheitsschluessel-im-betrieb',
            'lang' => 'de',
            'category' => 'IT-Sicherheit',
            'title' => 'Passkeys und Sicherheitsschlüssel: Phishing-sichere Anmeldung für kleine Teams',
            'excerpt' => 'Passwörter werden gestohlen, SMS-Codes abgefangen. Ein Sicherheitsschlüssel macht Phishing praktisch wirkungslos. So führen Sie ihn im Team ein.',
            'meta' => 'Passkeys und Sicherheitsschlüssel wie YubiKey erklärt: warum sie Phishing stoppen, welche Konten zuerst dran sind und wie die Einführung im Team gelingt.',
            'tags' => 'passkeys, yubikey, zwei-faktor, phishing',
            'published' => '2026-09-03 09:00:00',
            'body' => <<<'MD'
**Kurz gesagt:** Ein Sicherheitsschlüssel ist ein kleiner USB- oder NFC-Stick, der die Anmeldung bestätigt. Er prüft selbst, ob die Website echt ist, deshalb läuft Phishing ins Leere. Für E-Mail, Bank und Cloud-Konten ist er heute der beste Schutz, den ein kleiner Betrieb für unter 60 Euro pro Person bekommt.

Die Produkte in diesem Beitrag sind Empfehlungen aus der Praxis. Die Links sind Partnerlinks: Kaufen Sie darüber, erhalte ich eine kleine Provision, der Preis ändert sich für Sie nicht.

## Was ist der Unterschied zwischen Passkey und Sicherheitsschlüssel?

Ein **Passkey** ersetzt das Passwort durch ein kryptografisches Schlüsselpaar. Er kann im Handy, im Passwortmanager oder auf einem **Sicherheitsschlüssel** liegen. Der Hardware-Schlüssel ist die robusteste Form: Er lässt sich nicht kopieren und funktioniert auch, wenn das Handy leer ist.

## Warum sind SMS-Codes nicht mehr genug?

SMS-Codes und Codes aus Authenticator-Apps lassen sich auf gefälschten Anmeldeseiten abfangen. Der Angreifer gibt sie in Echtzeit auf der echten Seite ein. Ein Sicherheitsschlüssel antwortet nur der echten Domain. Auf der gefälschten Seite passiert schlicht nichts.

## Welchen Sicherheitsschlüssel brauche ich?

Das hängt von den Anschlüssen ab. Für moderne Laptops und Smartphones mit USB-C und NFC:

{{produkt:yubikey-5c-nfc}}

Für ältere Rechner mit USB-A:

{{produkt:yubikey-5-nfc-usb-a}}

Wer nur FIDO2 und Passkeys braucht, nicht aber Zusatzfunktionen wie Smartcard oder Einmalpasswörter, spart mit dem einfacheren Modell:

{{produkt:yubico-security-key-c-nfc}}

## Warum immer zwei Schlüssel pro Person?

Ein Schlüssel kann verloren gehen. Wer nur einen registriert, sperrt sich im Ernstfall aus. Deshalb pro Person zwei Schlüssel registrieren: einen am Schlüsselbund, einen im Büro- oder Haussafe.

## Welche Konten sollten zuerst geschützt werden?

1. **E-Mail-Postfach:** Wer es kontrolliert, setzt alle anderen Passwörter zurück.
2. **Microsoft 365 oder Google Workspace.**
3. **Onlinebanking und Zahlungsanbieter.**
4. **Domain- und Hosting-Konto:** Hier hängt Ihre Website.
5. **Passwortmanager.**

## Und wie schütze ich Daten auf USB-Sticks?

Wer Kundendaten auf einem Stick transportieren muss, nutzt einen hardwareverschlüsselten Stick mit eigener PIN. Ohne PIN sind die Daten unlesbar, auch wenn der Stick verloren geht:

{{produkt:kingston-ironkey-vault-privacy-50c-32gb}}

## Häufige Fragen

### Funktioniert ein Sicherheitsschlüssel mit Microsoft 365 und Google?

Ja. Microsoft, Google, Apple, viele Banken und Passwortmanager unterstützen FIDO2-Schlüssel und Passkeys.

### Was passiert, wenn ich den Schlüssel verliere?

Sie melden sich mit dem Zweitschlüssel an und entfernen den verlorenen aus dem Konto. Ohne PIN kann ein Finder damit nichts anfangen.

### Braucht der Schlüssel eine Batterie oder Software?

Nein. Er bezieht Strom über USB oder NFC und funktioniert ohne Treiber in allen gängigen Browsern.

### Lohnt sich das auch für ein Team von drei Personen?

Gerade dort. Kleine Betriebe sind häufig Ziel von Phishing, weil sie seltener eine IT-Abteilung haben. Für drei Personen kostet der Schutz einmalig wenige hundert Euro.

Stand: Oktober 2026. Ich richte Sicherheitsschlüssel für Ihr Team ein und sichere die wichtigsten Konten gemeinsam mit Ihnen ab.
MD,
        ],
        [
            'slug' => 'passkeys-und-sicherheitsschluessel-im-betrieb',
            'lang' => 'en',
            'category' => 'IT security',
            'title' => 'Passkeys and security keys: phishing-proof sign-in for small teams',
            'excerpt' => 'Passwords get stolen, SMS codes get intercepted. A security key makes phishing practically useless. Here is how to introduce it in your team.',
            'meta' => 'Passkeys and hardware security keys like YubiKey explained: why they stop phishing, which accounts come first and how to roll them out in a team.',
            'tags' => 'passkeys, yubikey, two-factor, phishing',
            'published' => '2026-09-03 09:00:00',
            'body' => <<<'MD'
**In short:** A security key is a small USB or NFC device that confirms your sign-in. It checks by itself whether the website is genuine, so phishing comes to nothing. For email, banking and cloud accounts it is the best protection a small business can get today for under 60 euros per person.

The products in this article are recommendations from practice. The links are affiliate links: if you buy through them, I earn a small commission and your price does not change.

## What is the difference between a passkey and a security key?

A **passkey** replaces the password with a cryptographic key pair. It can live on a phone, in a password manager or on a **security key**. The hardware key is the most robust form: it cannot be copied and works even when the phone is flat.

## Why are SMS codes no longer enough?

SMS codes and authenticator app codes can be captured on fake sign-in pages. The attacker enters them on the real site in real time. A security key only answers the genuine domain. On the fake page simply nothing happens.

## Which security key do I need?

It depends on your ports. For modern laptops and smartphones with USB-C and NFC:

{{produkt:yubikey-5c-nfc-security-key}}

For older computers with USB-A:

{{produkt:yubikey-5-nfc-usb-a-security-key}}

If you only need FIDO2 and passkeys, not extras like smart card or one-time passwords, the simpler model saves money:

{{produkt:yubico-security-key-c-nfc-fido}}

## Why always two keys per person?

A key can get lost. Registering only one locks you out when it matters. So register two keys per person: one on the key ring, one in the office or home safe.

## Which accounts should be protected first?

1. **Email:** whoever controls it can reset every other password.
2. **Microsoft 365 or Google Workspace.**
3. **Online banking and payment providers.**
4. **Domain and hosting account:** your website depends on it.
5. **Password manager.**

## And how do I protect data on USB sticks?

If you have to carry client data on a stick, use a hardware-encrypted stick with its own PIN. Without the PIN the data is unreadable, even if the stick is lost:

{{produkt:kingston-ironkey-vault-privacy-50c-32gb-usb-c}}

## Frequently asked questions

### Does a security key work with Microsoft 365 and Google?

Yes. Microsoft, Google, Apple, many banks and password managers support FIDO2 keys and passkeys.

### What happens if I lose the key?

You sign in with the second key and remove the lost one from the account. Without the PIN a finder cannot do anything with it.

### Does the key need a battery or software?

No. It draws power over USB or NFC and works without drivers in all common browsers.

### Is it worth it for a team of three?

Especially there. Small businesses are frequent phishing targets because they rarely have an IT department. For three people the protection costs a few hundred euros, once.

Updated: October 2026. I set up security keys for your team and secure the most important accounts together with you.
MD,
        ],
    ];

    public function up(): void
    {
        PostSeeder::insert($this->getAdapter()->getConnection(), self::POSTS);
    }

    public function down(): void
    {
        PostSeeder::remove($this->getAdapter()->getConnection(), self::POSTS);
    }
}
