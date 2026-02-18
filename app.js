require('dotenv').config({
  path: '.env'
});
//WorkingDirectory=/var/www/metis-pdfgen
const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
const bwipjs = require('bwip-js');
const express = require('express');
const mysql = require('mysql2/promise');

const app = express();
const port = 3001;
const ticketsDir = path.resolve(__dirname, 'ticket/gen_pdfs');
if (!fs.existsSync(ticketsDir)) fs.mkdirSync(ticketsDir);

const MOD = 65537;
const mul = 73;

const logDir = path.resolve(__dirname, 'ticket/node-logs');
if (!fs.existsSync(logDir)) fs.mkdirSync(logDir);

const logFile = fs.createWriteStream(path.join(logDir, 'server.log'), { flags: 'a' });
const errorFile = fs.createWriteStream(path.join(logDir, 'error.log'), { flags: 'a' });

const origConsoleLog = console.log;
const origConsoleError = console.error;

// Timestamp hinzufügen
function getTimestamp() {
  return new Date().toISOString();
}

// Konsole umleiten
console.log = (...args) => {
	logFile.write(`[${getTimestamp()}] LOG: ${args.join(' ')}\n`);
	origConsoleLog(...args); // auch auf die Konsole ausgeben
};

console.error = (...args) => {
  	errorFile.write(`[${getTimestamp()}] ERROR: ${args.join(' ')}\n`);
	origConsoleError(...args);
};

function transform(x, key) {
  return ((x * mul) ^ key) % MOD;
}

function inverseTransform(y, key) {
  const afterXor = y ^ key;
  const invMul = modInverse(mul, MOD);

  return (afterXor * invMul) % MOD;
}

function modInverse(a, m) {
  let m0 = m, t, q;
  let x0 = 0, x1 = 1;

  if (m === 1) return 0;

  while (a > 1) {
    q = Math.floor(a / m);
    t = m;

    m = a % m;
    a = t;
    t = x0;

    x0 = x1 - q * x0;
    x1 = t;
  }

  return x1 < 0 ? x1 + m0 : x1;
}

function getBase64Image(filePath) {
  const image = fs.readFileSync(filePath);
  const ext = path.extname(filePath).substring(1);
  return `data:image/${ext};base64,${image.toString('base64')}`;
}

function generateBarcode(person_id, codeText, filePath) {
  return new Promise((resolve, reject) => {
    bwipjs.toBuffer({
      bcid: 'code128',
      text: codeText,

      scale: 3,
      height: 8,

      includetext: true,
      textxalign: 'center',
      textyoffset: 3,

      barcolor: 'FFFFFF',        // ⬅ weiße Balken
      textcolor: 'FFFFFF'
    }, (err, png) => {
      if (err) return reject(err);
      try {
        fs.mkdirSync(path.dirname(filePath), { recursive: true });
        fs.writeFileSync(filePath, png);
        console.log(`✅ Barcode gespeichert unter ${filePath}`);
        resolve(filePath);
      } catch (e) {
        reject(e);
      }
    });
  });
}

async function generatePDF(person_id) {
  const fileName = `ticket_person_${person_id}.pdf`;
  const outputPath = path.resolve(ticketsDir, fileName);

  const eventCode = 'U-SKM_';
  const key = parseInt(process.env.ENC_KEY);
  if (isNaN(key)) {
    console.error('❌ ENV KEY ist ungültig oder nicht gesetzt');
    process.exit(1);
  }

  //const numberCode = transform(person_id, key);
  const numberCode = person_id.padStart(4, '0');
  const codeText = eventCode + numberCode;
  const barcodePath = path.join(__dirname, 'ticket/barcodes', `${codeText}.png`);

  console.log('🔍 Generierter Code:', codeText);

  await generateBarcode(person_id, codeText, barcodePath);

  const logoBase64 = getBase64Image(path.resolve(__dirname, 'ticket/images/Metis.png'));
  const qrBase64 = getBase64Image(path.resolve(__dirname, 'ticket/images/qr-code.png'));
  const barcodeBase64 = getBase64Image(barcodePath);

  const conn = await mysql.createConnection({
    host: process.env.DB_HOST,
    user: process.env.DB_USERNAME,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME
  });

  const dbNameENV = process.env.DB_NAME;
  const [rows] = await conn.query("SELECT DATABASE() AS db");
  const dbNameSQL = rows[0].db;

  const [data] = await conn.execute(`
    SELECT 
      t.id AS person_id,
      t.vorname,
      t.nachname,
      t.email,
      t.ticketCount,
      t.day
    FROM 
      tickets t
    WHERE 
      t.id = ?
  `, [person_id]);

  const person = data[0];
  if (!person) throw new Error(`Keine Person mit ID ${person_id} gefunden`);

  const html = `
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Unmuted – Ticket</title>

  <style>
    @page {
      margin: 0;
      size: A4;
    }

    /* =========================
       Design Tokens – Unmuted
       ========================= */
    :root {
      --color-bg-main: #0f0f12;
      --color-bg-card: #1a1c22;
      --color-bg-soft: #242732;

      --color-accent: #950b25;
      --color-accent-dark: #6b0f1a;
      --color-accent-gold: #c8a96a;

      --color-text-primary: #e6e7eb;
      --color-text-secondary: #b5b8c2;
      --color-text-muted: #7e828f;

      --color-border-subtle: rgba(255,255,255,0.08);

      --shadow-soft: 0 15px 40px rgba(0,0,0,0.45);
      --radius: 14px;
    }

    /* =========================
       Base
       ========================= */
    body {
      margin: 0;
      padding: 0;
      font-family: 'Inter', 'Montserrat', Arial, sans-serif;
      background: linear-gradient(to bottom, #7a0c16 0%, #2b0a0e 70%);
      color: var(--color-text-primary);
    }

    /* =========================
       Ticket Container
       ========================= */
    .ticket {
      max-width: 820px;
      background: var(--color-bg-card);
      border-radius: var(--radius);
      box-shadow: var(--shadow-soft);
      display: grid;
      grid-template-rows: auto auto auto auto auto;
      aspect-ratio: 1 / 1.414;
      overflow: hidden;
    }

    /* =========================
       Header
       ========================= */
    header {
      display: grid;
      grid-template-columns: auto 1fr auto;
      align-items: center;
      padding: 1.5rem 2rem;
      background: linear-gradient(
        to right,
        var(--color-accent),
        var(--color-accent-dark)
      );
      color: #ffffff;
    }

    #event-logo img {
      height: 46px;
    }

    #ticketQr img {
      height: 80px;
    }

    .headliner {
      text-align: center;
    }

    .headliner h1 {
      margin: 0;
      font-size: 2.4rem;
      font-weight: 800;
      letter-spacing: -0.03em;
      font-style: italic;
    }

    .headliner p {
      margin-top: 0.35rem;
      font-size: 0.95rem;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      opacity: 0.9;
    }

    /* =========================
       Content
       ========================= */
    .info {
      padding: 2rem;
      display: grid;
      gap: 2rem;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: var(--color-bg-soft);
      border-radius: var(--radius);
      overflow: hidden;
    }

    table caption {
      padding: 1rem 1.25rem;
      background: rgba(0,0,0,0.35);
      color: var(--color-accent-gold);
      font-weight: 600;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      text-align: left;
    }

    table th,
    table td {
      padding: 0.75rem 1.25rem;
      border-bottom: 1px solid var(--color-border-subtle);
      font-size: 0.95rem;
    }

    table th {
      color: var(--color-text-secondary);
      font-weight: 500;
      width: 35%;
    }

    table tr:last-child th,
    table tr:last-child td {
      border-bottom: none;
    }

    /* =========================
       Barcode
       ========================= */
    .bc {
      padding: 0rem 2rem 1rem 2rem;
      display: flex;
      justify-content: center;
      align-items: center;
      background: radial-gradient(
        circle at center,
        rgba(255,255,255,0.05),
        rgba(255,255,255,0)
      );
    }

    .bc img {
      max-height: 130px;
    }

    /* =========================
       Conditions
       ========================= */
    #conditions-wrap {
        margin: 0 2rem 2rem;
    }

    #conditions {
        width: 100%;
    }


    #conditions td {
      font-size: 0.85rem;
      color: var(--color-text-muted);
      line-height: 1.6;
    }

    #conditions a {
      color: var(--color-accent-gold);
      text-decoration: none;
    }

    /* =========================
       Footer
       ========================= */
    footer {
      padding: 1rem 2rem;
      text-align: center;
      font-size: 0.75rem;
      color: var(--color-text-muted);
      background: rgba(0,0,0,0.25);
      letter-spacing: 0.08em;
    }
  </style>
</head>

<body>
  <section class="ticket">

    <!-- HEADER -->
    <header>
      <div id="event-logo">
        <img src="${logoBase64}" alt="Unmuted Logo">
      </div>

      <div class="headliner">
        <h1>Unmuted</h1>
        <p>Zeig, wer du bist!</p>
      </div>

      <div id="ticketQr">
        <img src="${qrBase64}" alt="QR-Code Ticket">
      </div>
    </header>

    <!-- CONTENT -->
    <div class="info">

      <table id="visitor">
        <caption>Ticketinhaber</caption>
        <tbody>
          <tr><th>Ticket-ID</th><td>${person_id}</td></tr>
          <tr><th>Vorname</th><td>${data[0].vorname}</td></tr>
          <tr><th>Nachname</th><td>${data[0].nachname}</td></tr>
          <tr><th>E-Mail</th><td>${data[0].email}</td></tr>
        </tbody>
      </table>

      <table id="eventInfo">
        <caption>Vorstellungsdetails</caption>
        <tbody>
          <tr>
            <th>Datum & Uhrzeit</th>
            <td>${data[0].day}</td>
          </tr>
          <tr>
            <th>Einlass</th>
            <td>30 Minuten vor Beginn</td>
          </tr>
          <tr>
            <th>Ort</th>
            <td>Grüne Turnhalle · Marie-Curie-Gymnasium</td>
          </tr>
          <tr>
            <th>Tickets</th>
            <td>${data[0].ticketCount}</td>
          </tr>
        </tbody>
      </table>

    </div>

    <!-- BARCODE -->
    <div class="bc">
      <img src="${barcodeBase64}" alt="Barcode">
    </div>

    <!-- CONDITIONS -->
    <div id="conditions-wrap">
        <table id="conditions">
            <caption>Hinweise</caption>
            <tr>
                <td>
                Dieses Ticket berechtigt zum Eintritt zur oben genannten Vorstellung
                von <strong>Unmuted – Zeig, wer du bist!</strong>.<br>
                Bitte halten Sie dieses Ticket (digital oder ausgedruckt) beim Einlass bereit.
                <br>
                Einlass nur zur gebuchten Vorstellung. Kein Wiedereinlass nach Verlassen des Saals.
                <br><br>
                Weitere Informationen unter:
                <a href="https://www.curiegymnasium.de">
                    curiegymnasium.de
                </a>
                </td>
            </tr>
        </table>
    </div>

    <!-- FOOTER -->
    <footer>
      Unmuted · SK Musical · Alle Angaben ohne Gewähr · Powered by Metis
    </footer>

  </section>
</body>
</html>
  `

  const browser = await puppeteer.launch({ headless: true, product: 'firefox' });
  const page = await browser.newPage();

  await page.setContent(html, { waitUntil: 'domcontentloaded' });
  await page.pdf({
    path: outputPath,
    format: 'A4',
    printBackground: true,
    margin: { top: '0mm', bottom: '0mm', left: '0mm', right: '0mm' }
  });

  await browser.close();
  console.log(`✅ PDF wurde erstellt: ${outputPath}`);
  return Buffer.from(fs.readFileSync(outputPath), "utf8").toString('base64');
}

app.get('/', async (req, res) => {
  if (!req.query.person_id) return res.status(400).send('fail');

  try {
    const pdf = await generatePDF(req.query.person_id);
    //res.send('success');
    res.json({ status: 'success', pdf });
  } catch (err) {
    console.error('❌ Fehler bei PDF-Erstellung:', err);
    res.status(500).send('fail');
  }
});

app.listen(port, () => {
  console.log(`listening on port ${port}`);
});
