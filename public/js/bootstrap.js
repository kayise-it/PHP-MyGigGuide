import axios from 'https://cdn.skypack.dev/axios@1.7.9';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';


