<template>
    <div>
        <div class="alert alert-danger alert-dismissible fade show" role="alert" v-if="errorMSG != null">
            <span style="cursor:pointer" v-on:click="changeCollapse" data-bs-toggle="collapse"
                data-bs-target="#collapseMessage" aria-expanded="false" aria-controls="collapseMessage">
                <i :class="'fa-solid fa-caret-' + collapse" style="padding:10px; "></i>
                {{ $t('An error occurred while saving the data') }}
            </span>
            <div class="collapse collapse-vertical" id="collapseMessage">
                <div class="card card-body"> {{ errorMSG }} </div>
            </div>
            <button type="button" class="btn-close" v-on:click="cleanError" data-bs-dismiss="alert"
                aria-label="Close"></button>
        </div>
        <div class="alert alert-success dispel_hidden" role="alert" v-if="successMSG != null">
            {{ successMSG }}
        </div>
    </div>
</template>

<script>
export default {
    props: ['listener'],
    name: "AlertComponent",
    data() {
        return {
            errorMSG: null,
            successMSG: null,
            collapse: 'down'
        }
    },
    mounted() {
        this.emitter.on('alert_' + this.listener, parameter => {
            if (parameter.success) this.successMSG = parameter.txt;
            else this.errorMSG = parameter.txt;
            setTimeout(()=>{
                this.errorMSG = null;
                this.successMSG =  null;
            }, 3000);
        });
    },
    methods: {
        changeCollapse: function () {
            this.collapse = this.collapse === 'down' ? 'up' : 'down';
        },
        cleanError: function () {
            this.errorMSG = null;
        }
    }
}
</script>

<style scoped>
@-webkit-keyframes fadeOut {
    0% {
        opacity: 1;
    }

    80% {
        opacity: 0.20;
        width: 100%;
        height: 100%;
    }

    100% {
        opacity: 0;
        width: 0;
        height: 0;
        display: none;
    }
}

@keyframes fadeOut {
    0% {
        opacity: 1;
    }

    80% {
        opacity: 0.20;
        width: 100%;
        height: 100%;
    }

    100% {
        opacity: 0;
        width: 0;
        height: 0;
        display: none;
    }
}

.dispel_hidden {
    display: block;
    -webkit-animation: fadeOut 5s;
    animation: fadeOut 5s;
    animation-fill-mode: forwards;
}
</style>
