const express = require('express');
const http = require('http');
const WebSocket = require('ws');
const path = require('path');

const app = express();
const server = http.createServer(app);
const wss = new WebSocket.Server({ 
    server,
    // Permettre les connexions de toutes les origines
    verifyClient: () => true,
    // Augmenter la taille maximale des messages
    maxPayload: 65536
});

const clients = new Map();

wss.on('connection', function connection(ws, req) {
    console.log('New client connected from:', req.socket.remoteAddress);
    
    const id = req.headers['sec-websocket-key'];
    ws.userId = null;
    ws.clientIp = req.socket.remoteAddress;
    clients.set(id, ws);

    ws.isAlive = true;
    ws.on('pong', () => {
        ws.isAlive = true;
    });

    ws.on('message', function incoming(message) {
        try {
            const data = JSON.parse(message);
            console.log('Received message type:', data.type, 'from IP:', ws.clientIp);

            // Gestion spéciale des candidats ICE
            if (data.type === 'ice-candidate') {
                console.log('ICE candidate for target:', data.target);
                const targetClient = Array.from(clients.values()).find(client => 
                    client.userId && client.userId.toString() === data.target.toString()
                );

                if (targetClient && targetClient.readyState === WebSocket.OPEN) {
                    console.log('Forwarding ICE from', ws.userId, 'to', targetClient.userId);
                    targetClient.send(JSON.stringify({
                        type: 'ice-candidate',
                        from: ws.userId,
                        candidate: data.candidate
                    }));
                } else {
                    console.log('Target not found for ICE candidate');
                }
                return;
            }

            console.log('Received message:', data, 'from user:', ws.userId);

            // Si c'est un message d'identification
            if (data.type === 'identify') {
                ws.userId = data.userId;
                console.log(`Client ${id} (${ws.clientIp}) identified as user ${data.userId}`);
                return;
            }

            // Pour les appels, vérifier spécifiquement le type de message
            if (data.type === 'call-offer' || data.type === 'call-answer' || 
                data.type === 'ice-candidate' || data.type === 'call-ended') {
                console.log('Processing call message:', data.type, 'target:', data.target);
                const targetClient = Array.from(clients.values()).find(client => {
                    return client.userId && client.userId.toString() === data.target.toString();
                });

                if (targetClient && targetClient.readyState === WebSocket.OPEN) {
                    console.log('Sending to target user:', targetClient.userId);
                    targetClient.send(JSON.stringify({
                        ...data,
                        from: ws.userId
                    }));
                } else {
                    console.log('Target client not found or not connected');
                    ws.send(JSON.stringify({
                        type: 'call-error',
                        message: 'Utilisateur non disponible'
                    }));
                }
                return;
            }

            if (data.target) {
                console.log('Searching for target:', data.target);
                // Amélioration de la recherche du client cible
                const targetClient = Array.from(clients.values()).find(client => {
                    console.log('Checking client userId:', client.userId, 'against target:', data.target);
                    return client.userId && client.userId.toString() === data.target.toString();
                });
                
                if (targetClient && targetClient.readyState === WebSocket.OPEN) {
                    console.log('Sending call to target:', data.target, 'from:', ws.userId);
                    targetClient.send(JSON.stringify({
                        ...data,
                        from: ws.userId,
                        caller: ws.userId // Ajout de l'identifiant de l'appelant
                    }));
                } else {
                    console.log('Target not found or not connected:', data.target);
                    // Informer l'appelant que la cible n'est pas disponible
                    ws.send(JSON.stringify({
                        type: 'call-error',
                        message: 'Utilisateur non disponible'
                    }));
                }
            } else {
                // Broadcast
                wss.clients.forEach(function each(client) {
                    if (client !== ws && client.readyState === WebSocket.OPEN) {
                        client.send(JSON.stringify({...data, from: ws.userId}));
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

// Permettre l'accès depuis le dossier chat-app
app.use(express.static(path.join(__dirname, 'chat-app', 'public')));

// Autoriser les connexions cross-origin
app.use((req, res, next) => {
    res.header('Access-Control-Allow-Origin', '*');
    res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept');
    next();
});

// Démarrage du serveur
const PORT = process.env.PORT || 8090;
server.listen(PORT, () => {
    console.log(`WebSocket server is running on port ${PORT}`);
    console.log(`Using node_modules from: ${path.resolve(__dirname, 'node_modules')}`);
});