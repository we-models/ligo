import 'bootstrap';

/**
 * Cargaremos la biblioteca axios HTTP, que nos permite emitir fácilmente solicitudes
 * a nuestro backend de Laravel. Esta biblioteca maneja automáticamente el envío del
 * token CSRF como encabezado según el valor de la cookie del token "XSRF".
 */
import axios from 'axios';
import $ from "jquery";
import Swal from 'sweetalert2'
// import { createPopper } from '@popperjs/core';

(window as any).axios = axios;
(window as any).$ = $;
(window as any).jQuery = $;
(window as any).Swal = Swal;
// (window as any).Popper = { createPopper };

(window as any).axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
