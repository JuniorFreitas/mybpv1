/*
Give the service worker access to Firebase Messaging.
Note that you can only use Firebase Messaging here, other Firebase libraries are not available in the service worker.
*/
importScripts('https://www.gstatic.com/firebasejs/7.23.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/7.23.0/firebase-messaging.js');

/*
Initialize the Firebase app in the service worker by passing in the messagingSenderId.
* New configuration for app@pulseservice.com
*/
firebase.initializeApp({
    apiKey: "AIzaSyBIB10-Piv01Sf49pme8ZxN5LjF-42X7t0",
    authDomain: "pushchefe.firebaseapp.com",
    databaseURL: "https://pushchefe.firebaseio.com",
    projectId: "pushchefe",
    storageBucket: "pushchefe.appspot.com",
    messagingSenderId: "393104162511",
    appId: "1:393104162511:web:bc53fec34d4b5a14c4c1ba",
    measurementId: "G-H25CH1VLCT"
    // measurementId: "G-R1KQTR3JBN"
});

/*
Retrieve an instance of Firebase Messaging so that it can handle background messages.
*/
const messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function(payload) {
    console.log(
        "[firebase-messaging-sw.js] Received background message ",
        payload,
    );
    // Customize notification here
    const notificationTitle = "Background Message Title";
    const notificationOptions = {
        body: "Background Message body.",
        icon: "/itwonders-web-logo.png",
    };

    return self.registration.showNotification(
        notificationTitle,
        notificationOptions,
    );
});
