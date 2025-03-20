class CallManager {
    constructor(websocket) {
        this.peerConnection = null;
        this.websocket = websocket;
        this.localStream = null;
        this.remoteStream = null;
        
        this.configuration = {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' }
            ]
        };

        // Ajout d'un état pour suivre l'appel
        this.isCallInProgress = false;
        
        // Vérification de la connexion WebSocket
        if (!websocket) {
            throw new Error('WebSocket connection is required');
        }
        this.websocket = websocket;

        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 3;
        this.setupWebSocket(websocket);
    }

    setupWebSocket(websocket) {
        this.websocket = websocket;
        
        this.websocket.onclose = async () => {
            console.log('WebSocket connection closed');
            if (this.reconnectAttempts < this.maxReconnectAttempts) {
                this.reconnectAttempts++;
                await this.reconnectWebSocket();
            }
        };
    }

    async reconnectWebSocket() {
        try {
            console.log('Attempting to reconnect...');
            const ws = new WebSocket('ws://localhost:8090');
            this.setupWebSocket(ws);
        } catch (error) {
            console.error('Reconnection failed:', error);
        }
    }

    async sendWebSocketMessage(data) {
        if (this.websocket.readyState === WebSocket.OPEN) {
            this.websocket.send(JSON.stringify(data));
        } else {
            throw new Error('WebSocket connection is not open');
        }
    }

    async startCall(userId) {
        console.log('Starting call to user:', userId);
        if (this.isCallInProgress) {
            throw new Error('Call already in progress');
        }

        try {
            this.targetUserId = userId; // Stocker l'ID de l'utilisateur cible
            this.localStream = await navigator.mediaDevices.getUserMedia({ 
                audio: true,
                video: false 
            });

            this.peerConnection = new RTCPeerConnection(this.configuration);
            
            // Add all tracks from local stream to peer connection
            this.localStream.getTracks().forEach(track => {
                console.log('Adding track to peer connection:', track.kind);
                this.peerConnection.addTrack(track, this.localStream);
            });

            // Set up peer connection listeners before creating offer
            this.setupPeerConnectionListeners();

            const offer = await this.peerConnection.createOffer({
                offerToReceiveAudio: true
            });
            
            await this.peerConnection.setLocalDescription(offer);
            console.log('Local description set, sending offer to:', userId);

            await this.sendWebSocketMessage({
                type: 'call-offer',
                target: userId,
                offer: offer
            });
            
            this.isCallInProgress = true;
        } catch (error) {
            this.handleError(error);
            throw error;
        }
    }

    async answerCall(callId, offer) {
        try {
            this.localStream = await navigator.mediaDevices.getUserMedia({ audio: true });
            this.peerConnection = new RTCPeerConnection(this.configuration);
            
            this.localStream.getTracks().forEach(track => {
                this.peerConnection.addTrack(track, this.localStream);
            });

            await this.peerConnection.setRemoteDescription(offer);
            const answer = await this.peerConnection.createAnswer();
            await this.peerConnection.setLocalDescription(answer);

            this.websocket.send(JSON.stringify({
                type: 'call-answer',
                target: callId,
                answer: answer
            }));

            this.setupPeerConnectionListeners();
        } catch (error) {
            console.error('Erreur lors de la réponse à l\'appel:', error);
            throw error;
        }
    }

    async handleAnswer(answer) {
        if (this.peerConnection) {
            await this.peerConnection.setRemoteDescription(answer);
        }
    }

    async addIceCandidate(candidate) {
        try {
            if (this.peerConnection && candidate) {
                console.log('Adding ICE candidate:', candidate);
                // Créer un nouvel objet RTCIceCandidate avec les propriétés correctes
                const iceCandidate = new RTCIceCandidate({
                    candidate: candidate.candidate,
                    sdpMid: candidate.sdpMid,
                    sdpMLineIndex: candidate.sdpMLineIndex,
                    usernameFragment: candidate.usernameFragment
                });
                await this.peerConnection.addIceCandidate(iceCandidate);
                console.log('ICE candidate added successfully');
            }
        } catch (error) {
            console.error('Error adding ICE candidate:', error);
        }
    }

    setupPeerConnectionListeners() {
        this.peerConnection.ontrack = (event) => {
            this.remoteStream = event.streams[0];
            // Déclencher l'événement pour mettre à jour l'interface
            document.dispatchEvent(new CustomEvent('remoteStreamReceived', {
                detail: { stream: this.remoteStream }
            }));
        };

        this.peerConnection.onicecandidate = (event) => {
            if (event.candidate) {
                console.log('Generated ICE candidate for:', this.targetUserId);
                try {
                    this.websocket.send(JSON.stringify({
                        type: 'ice-candidate',
                        target: this.targetUserId,
                        candidate: {
                            candidate: event.candidate.candidate,
                            sdpMid: event.candidate.sdpMid,
                            sdpMLineIndex: event.candidate.sdpMLineIndex,
                            usernameFragment: event.candidate.usernameFragment
                        }
                    }));
                } catch (error) {
                    console.error('Error sending ICE candidate:', error);
                }
            }
        };

        this.peerConnection.oniceconnectionstatechange = () => {
            console.log('ICE state:', this.peerConnection.iceConnectionState);
            switch (this.peerConnection.iceConnectionState) {
                case 'connected':
                    console.log('ICE connection established');
                    break;
                case 'failed':
                    console.error('ICE connection failed');
                    this.handleError(new Error('ICE connection failed'));
                    break;
            }
        };
    }

    endCall() {
        if (this.peerConnection) {
            this.peerConnection.close();
            this.peerConnection = null;
        }
        
        if (this.localStream) {
            this.localStream.getTracks().forEach(track => track.stop());
            this.localStream = null;
        }

        this.remoteStream = null;
        this.isCallInProgress = false;
    }

    handleError(error) {
        console.error('CallManager error:', error);
        this.endCall();
        this.isCallInProgress = false;
    }
}

// Export pour utilisation dans d'autres fichiers
window.CallManager = CallManager;
