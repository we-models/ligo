import {computed, onMounted, ref} from "vue";

export default {
    name: "HomeView",
    setup(props: any) {

        const welcome = ref<string>("¡Hello world");

        const welcomePerson = computed(() => {
            return `${welcome.value} person`;
        });

        const time = ref(new Date());

        onMounted(() => {
            setInterval(changeTime, 1000);
        })

        const changeTime = () => {
            time.value = new Date();
        }

        return {
            welcome,
            welcomePerson,
            time
        }
    }
}
