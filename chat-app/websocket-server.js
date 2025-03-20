const express = require('express');
const http = require('http');
const WebSocket = require('ws');

const app = express();
const server = http.createServer(app);
const wss = new WebSocket.Server({ server });

const clients = new Map();

wss.on('connection', function connection(ws, req) {
    console.log('New client connected');
    
    // Stocker l'ID du client
    const id = req.headers['sec-websocket-key'];
    clients.set(id, ws);

    ws.isAlive = true;
    ws.on('pong', () => {
        ws.isAlive = true;
    });

    ws.on('message', function incoming(message) {
        try {
            const data = JSON.parse(message);
            console.log('Received:', data);

            if (data.target) {
                // Envoyer le message au destinataire spécifique
                const targetClient = Array.from(clients.values()).find(client => 
                    client.userId === data.target
                );
                
                if (targetClient && targetClient.readyState === WebSocket.OPEN) {
                    targetClient.send(JSON.stringify({
                        ...data,
                        from: id
                    }));
                }
            } else {
                // Broadcast si pas de destinataire spécifique
                wss.clients.forEach(function each(client) {
                    if (client !== ws && client.readyState === WebSocket.OPEN) {
                        client.send(JSON.stringify({...data, from: id}));
                    }
                });
            }
        } catch (error) {
            console.error('Error processing message:', error);
        }
    });

    ws.on('close', () => {
        console.log('Client disconnected:', id);
        clients.delete(id);
    });

    // Envoyer un message de confirmation de connexion
    ws.send(JSON.stringify({
        type: 'connection',
        status: 'connected',
        id: id
    }));
});

// Ping pour maintenir les connexions actives
const interval = setInterval(() => {
    wss.clients.forEach((ws) => {
        if (ws.isAlive === false) {
            clients.delete(ws.id);
            return ws.terminate();
        }
        ws.isAlive = false;
        ws.ping();
    });
}, 30000);

wss.on('close', () => clearInterval(interval));

// Démarrage du serveur
const PORT = process.env.PORT || 8090;
server.listen(PORT, () => {
    console.log(`WebSocket server is running on port ${PORT}`);
});
