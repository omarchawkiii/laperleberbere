<?php
/**
 * Configuration Email
 * LA PERLE BERBÈRE
 * ──────────────────────────────────────────
 * Modifiez les valeurs ci-dessous pour
 * configurer l'envoi d'emails.
 * ──────────────────────────────────────────
 */

// ── Email de l'administrateur (destinataire des notifications) ──
define('MAIL_ADMIN',     'email@gmail.com');

// ── Expéditeur affiché dans les emails ──
define('MAIL_FROM',      'email@gmail.com');
define('MAIL_FROM_NAME', 'LA PERLE BERBÈRE');

// ── SMTP ────────────────────────────────────────────────────────
define('MAIL_USE_SMTP',  true);
define('MAIL_SMTP_HOST', 'mail.omodiz.com');
define('MAIL_SMTP_PORT', 465);
define('MAIL_SMTP_USER', 'email@gmail.com');
define('MAIL_SMTP_PASS', 'Utilisez le mot de passe du compte de messagerie.');  // ← remplacez par votre vrai mot de passe
define('MAIL_SMTP_TLS',  false);              // false = SSL direct (port 465)

// ================================================================
//  Fonction d'envoi — ne pas modifier
// ================================================================

/**
 * Envoie une notification email à l'admin lors d'un nouveau rendez-vous.
 *
 * @param array $booking  Données du rendez-vous
 * @param array $slot     Données du créneau (date, heure)
 * @return bool
 */
function send_booking_notification(array $booking, array $slot): bool
{
    $to      = MAIL_ADMIN;
    $subject = '=?UTF-8?B?' . base64_encode('Nouveau rendez-vous — LA PERLE BERBÈRE') . '?=';
    $body    = build_email_body($booking, $slot);

    if (MAIL_USE_SMTP) {
        return send_via_smtp($to, $subject, $body);
    }

    // Fallback : fonction mail() native PHP
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM . ">\r\n";
    $headers .= "Reply-To: " . $booking['email'] . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    return (bool) mail($to, $subject, $body, $headers);
}

/**
 * Construit le corps HTML de l'email.
 */
function build_email_body(array $booking, array $slot): string
{
    $accent   = '#e74c3c';
    $prenom   = htmlspecialchars($booking['prenom']);
    $nom      = htmlspecialchars($booking['nom']);
    $email    = htmlspecialchars($booking['email']);
    $tel      = htmlspecialchars($booking['telephone']);
    $raison   = !empty($booking['raison']) ? htmlspecialchars($booking['raison']) : '<em style="color:#999;">Non renseignée</em>';

    // Formatage de la date
    $date_ts  = strtotime($slot['date']);
    $days_fr  = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
    $months_fr= ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
    $date_fmt = $days_fr[date('w', $date_ts)] . ' ' . date('d', $date_ts) . ' ' . $months_fr[(int)date('n', $date_ts)] . ' ' . date('Y', $date_ts);
    $heure    = substr($slot['heure'], 0, 5) . 'h';
    $now      = date('d/m/Y à H:i');

    return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:'Segoe UI',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f9;padding:30px 15px;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0"
             style="background:white;border-radius:12px;overflow:hidden;box-shadow:0 4px 16px rgba(0,0,0,.1);max-width:100%;">

        <!-- En-tête -->
        <tr>
          <td style="background:$accent;padding:28px 30px;text-align:center;">
            <h1 style="margin:0;color:white;font-size:22px;font-weight:700;letter-spacing:.5px;">
              LA PERLE BERBÈRE
            </h1>
            <p style="margin:6px 0 0;color:rgba(255,255,255,.85);font-size:14px;">
              Nouveau rendez-vous reçu
            </p>
          </td>
        </tr>

        <!-- Alerte -->
        <tr>
          <td style="padding:24px 30px 0;">
            <div style="background:#fde8e6;border-left:4px solid $accent;border-radius:6px;padding:14px 18px;">
              <p style="margin:0;color:#c0392b;font-weight:600;font-size:15px;">
                📅 Un nouveau rendez-vous vient d'être réservé
              </p>
              <p style="margin:4px 0 0;color:#555;font-size:13px;">
                Reçu le $now
              </p>
            </div>
          </td>
        </tr>

        <!-- Infos client -->
        <tr>
          <td style="padding:24px 30px 0;">
            <h2 style="color:#2c3e50;font-size:16px;margin:0 0 14px;border-bottom:2px solid #f0f0f0;padding-bottom:8px;">
              👤 Informations du client
            </h2>
            <table width="100%" cellpadding="6" cellspacing="0">
              <tr>
                <td style="color:#888;font-size:13px;width:130px;">Nom complet</td>
                <td style="font-weight:600;color:#2c3e50;font-size:14px;">$prenom $nom</td>
              </tr>
              <tr style="background:#f9f9f9;border-radius:6px;">
                <td style="color:#888;font-size:13px;padding:8px 6px;">Email</td>
                <td style="font-size:14px;padding:8px 6px;">
                  <a href="mailto:$email" style="color:$accent;text-decoration:none;">$email</a>
                </td>
              </tr>
              <tr>
                <td style="color:#888;font-size:13px;">Téléphone</td>
                <td style="font-size:14px;">
                  <a href="tel:$tel" style="color:$accent;text-decoration:none;">$tel</a>
                </td>
              </tr>
              <tr style="background:#f9f9f9;">
                <td style="color:#888;font-size:13px;padding:8px 6px;">Raison</td>
                <td style="font-size:14px;padding:8px 6px;">$raison</td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Créneau -->
        <tr>
          <td style="padding:20px 30px 0;">
            <h2 style="color:#2c3e50;font-size:16px;margin:0 0 14px;border-bottom:2px solid #f0f0f0;padding-bottom:8px;">
              📆 Créneau réservé
            </h2>
            <div style="background:$accent;color:white;border-radius:10px;padding:16px 20px;display:inline-block;width:100%;box-sizing:border-box;">
              <div style="font-size:18px;font-weight:700;">$date_fmt</div>
              <div style="font-size:15px;margin-top:4px;opacity:.9;">🕐 $heure</div>
            </div>
          </td>
        </tr>

        <!-- CTA -->
        <tr>
          <td style="padding:24px 30px;">
            <a href="https://laperleberbere.omodiz.com/admin/"
               style="display:inline-block;background:$accent;color:white;text-decoration:none;
                      padding:12px 24px;border-radius:8px;font-weight:600;font-size:14px;">
              Voir le tableau de bord →
            </a>
          </td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="background:#f4f6f9;padding:16px 30px;text-align:center;border-top:1px solid #eee;">
            <p style="margin:0;color:#999;font-size:12px;">
              LA PERLE BERBÈRE — Système de gestion des rendez-vous
            </p>
          </td>
        </tr>

      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
}

/**
 * Envoi via SMTP natif PHP (sans dépendance externe).
 * Supporte STARTTLS (port 587) et SSL (port 465).
 */
function send_via_smtp(string $to, string $subject, string $body): bool
{
    $host = MAIL_SMTP_HOST;
    $port = MAIL_SMTP_PORT;
    $user = MAIL_SMTP_USER;
    $pass = MAIL_SMTP_PASS;
    $tls  = MAIL_SMTP_TLS;
    $from = MAIL_FROM;

    try {
        $prefix = $tls ? 'tcp' : 'ssl';
        $conn   = fsockopen($prefix . '://' . $host, $port, $errno, $errstr, 10);
        if (!$conn) return false;

        $read = function() use ($conn) {
            $data = '';
            while ($line = fgets($conn, 515)) {
                $data .= $line;
                if (substr($line, 3, 1) === ' ') break;
            }
            return $data;
        };

        $cmd = function(string $c) use ($conn, $read) {
            fputs($conn, $c . "\r\n");
            return $read();
        };

        $read(); // banner
        $cmd('EHLO ' . gethostname());

        if ($tls) {
            $cmd('STARTTLS');
            stream_socket_enable_crypto($conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $cmd('EHLO ' . gethostname());
        }

        $cmd('AUTH LOGIN');
        $cmd(base64_encode($user));
        $cmd(base64_encode($pass));
        $cmd('MAIL FROM:<' . $from . '>');
        $cmd('RCPT TO:<' . $to . '>');
        $cmd('DATA');

        $message  = "From: " . MAIL_FROM_NAME . " <$from>\r\n";
        $message .= "To: $to\r\n";
        $message .= "Subject: $subject\r\n";
        $message .= "MIME-Version: 1.0\r\n";
        $message .= "Content-Type: text/html; charset=UTF-8\r\n";
        $message .= "\r\n" . $body . "\r\n.";

        $cmd($message);
        $cmd('QUIT');
        fclose($conn);
        return true;

    } catch (Exception $e) {
        return false;
    }
}
