import axios from 'axios';
import '../../vendor/masmerise/livewire-toaster/resources/js';
import {browserSupportsWebAuthn, startAuthentication, startRegistration, } from '@simplewebauthn/browser';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.startRegistration = startRegistration;
window.browserSupportsWebAuthn = browserSupportsWebAuthn;
window.startAuthentication = startAuthentication;
