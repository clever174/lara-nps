import '../css/app.css'
// import './bootstrap'

import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createApp, h } from 'vue'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'

// PrimeVu
import PrimeVue from 'primevue/config'
import Aura from '@primeuix/themes/aura'
import 'primeicons/primeicons.css'

const appName = import.meta.env.VITE_APP_NAME || 'NPS'

createInertiaApp({
    title: (title) => `${title}`,
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(PrimeVue, {
                theme: {
                    preset: Aura
                },
                locale: {
                    firstDayOfWeek: 1,
                    dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
                    dayNamesShort: ['вс','пн','вт','ср','чт','пт','сб'],
                    dayNamesMin: ['вс','пн','вт','ср','чт','пт','сб'],
                    monthNames: [
                        'январь','февраль','март','апрель','май','июнь',
                        'июль','август','сентябрь','октябрь','ноябрь','декабрь'
                    ],
                    monthNamesShort: [
                        'янв','фев','мар','апр','май','июн',
                        'июл','авг','сен','окт','ноя','дек'
                    ],
                    today: 'Сегодня',
                    clear: 'Очистить'
                }
            })
            .mount(el)
    },
    progress: {
        color: '#4B5563',
    },
})
