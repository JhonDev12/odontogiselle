const express = require("express");
const {
    default: makeWASocket,
    useMultiFileAuthState,
    DisconnectReason,
} = require("@whiskeysockets/baileys");
const { Boom } = require("@hapi/boom");
const fs = require("fs");
const path = require("path");
const qrcode = require("qrcode-terminal");
const cors = require("cors");

const app = express();
const PORT = 3000;

app.use(express.json());
app.use(cors());

let globalSocket = null;

async function startBot() {
    const { state, saveCreds } = await useMultiFileAuthState(
        path.resolve(__dirname, "baileys_auth")
    );

    const sock = makeWASocket({
        auth: state,
        printQRInTerminal: false,
    });

    globalSocket = sock;

    sock.ev.on("creds.update", saveCreds);

    sock.ev.on("connection.update", (update) => {
        const { connection, lastDisconnect, qr } = update;

        if (qr) {
            console.log("📲 Escanea este código QR desde WhatsApp");
            qrcode.generate(qr, { small: true });
        }

        if (connection === "close") {
            const shouldReconnect =
                lastDisconnect?.error?.output?.statusCode !==
                DisconnectReason.loggedOut;
            console.log("🔁 Conexión cerrada. ¿Reconectar?", shouldReconnect);
            if (shouldReconnect) {
                startBot();
            }
        } else if (connection === "open") {
            console.log(" Conectado a WhatsApp correctamente");
        }
    });
}

app.post("/enviar-mensaje", async (req, res) => {
    const { numero, mensaje } = req.body;

    if (!numero || !mensaje) {
        return res
            .status(400)
            .json({ error: "Faltan datos requeridos: numero o mensaje." });
    }

    if (!globalSocket) {
        return res
            .status(500)
            .json({ error: "WhatsApp no está conectado aún." });
    }

    try {
        await globalSocket.sendMessage(`${numero}@s.whatsapp.net`, {
            text: mensaje,
        });
        console.log(`Mensaje enviado a ${numero}: ${mensaje}`);
        res.status(200).json({ estado: "Mensaje enviado correctamente." });
    } catch (error) {
        console.error("Error al enviar mensaje:", error);
        res.status(500).json({
            error: "No se pudo enviar el mensaje por WhatsApp.",
        });
    }
});

app.listen(PORT, () => {
    console.log(` Servidor Node.js corriendo en http://localhost:${PORT}`);
    startBot();
});
