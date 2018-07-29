import axios from 'axios';
import { showAjaxError } from './notify';

axios.defaults.baseURL = blessing.base_url;
axios.defaults.validateStatus = status => (status >= 200 && status < 300) || status === 422;

axios.interceptors.response.use(
    response => response,
    showAjaxError
);
