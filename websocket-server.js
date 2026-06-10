const WebSocket = require('ws');
const http = require('http');

const server = http.createServer();
const wss = new WebSocket.Server({ server });

const PORT = process.env.WEBSOCKET_PORT || 6001;
const clients = new Map(); // Untuk track subscriber per channel

console.log(`🚀 WebSocket server running on ws://localhost:${PORT}`);

wss.on('connection', (ws) => {
    console.log('📱 Client connected');

    ws.on('message', (message) => {
        try {
            const data = JSON.parse(message);

            if (data.type === 'subscribe') {
                // Client subscribe ke channel
                const channel = data.channel;
                if (!clients.has(channel)) {
                    clients.set(channel, new Set());
                }
                clients.get(channel).add(ws);
                ws.channel = channel;
                console.log(`✅ Client subscribed to channel: ${channel}`);

                // Send confirmation
                ws.send(JSON.stringify({
                    type: 'subscription_confirmed',
                    channel: channel,
                }));
            } else if (data.type === 'unsubscribe') {
                const channel = data.channel;
                if (clients.has(channel)) {
                    clients.get(channel).delete(ws);
                    console.log(`❌ Client unsubscribed from channel: ${channel}`);
                }
            } else if (data.type === 'broadcast') {
                // Backend broadcast ke channel
                const channel = data.channel;
                const payload = data.payload;

                if (clients.has(channel)) {
                    clients.get(channel).forEach((client) => {
                        if (client.readyState === WebSocket.OPEN) {
                            client.send(JSON.stringify({
                                type: 'message',
                                channel: channel,
                                data: payload,
                                timestamp: new Date().toISOString(),
                            }));
                        }
                    });
                    console.log(`📢 Broadcast to ${channel}: ${JSON.stringify(payload).substring(0, 100)}...`);
                }
            }
        } catch (error) {
            console.error('❌ Error processing message:', error.message);
        }
    });

    ws.on('close', () => {
        // Cleanup
        if (ws.channel && clients.has(ws.channel)) {
            clients.get(ws.channel).delete(ws);
        }
        console.log('🔌 Client disconnected');
    });

    ws.on('error', (error) => {
        console.error('❌ WebSocket error:', error.message);
    });
});

// API untuk backend send broadcast
const expressApp = require('express')();
expressApp.use(require('express').json());

expressApp.post('/api/broadcast', (req, res) => {
    const { channel, data } = req.body;

    if (!channel || !data) {
        return res.status(400).json({ error: 'channel and data required' });
    }

    if (clients.has(channel)) {
        clients.get(channel).forEach((client) => {
            if (client.readyState === WebSocket.OPEN) {
                client.send(JSON.stringify({
                    type: 'message',
                    channel: channel,
                    data: data,
                    timestamp: new Date().toISOString(),
                }));
            }
        });
        console.log(`📢 API Broadcast to ${channel}:`, data);
        res.json({ success: true, recipients: clients.get(channel).size });
    } else {
        res.json({ success: true, recipients: 0, message: 'No subscribers' });
    }
});

const httpServer = http.createServer(expressApp);
httpServer.on('upgrade', (request, socket, head) => {
    wss.handleUpgrade(request, socket, head, (ws) => {
        wss.emit('connection', ws, request);
    });
});

httpServer.listen(PORT, () => {
    console.log(`✅ WebSocket server + HTTP API listening on port ${PORT}`);
});
