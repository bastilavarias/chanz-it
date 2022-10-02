<script>
import { ref } from 'vue';

export default {
    name: 'CustomButton',

    props: {
        icon: [Boolean, String, null]
    },

    setup() {
        const isOpen = ref(true);

        return {
            isOpen
        };
    }
};
</script>

<template>
    <button
        type="button"
        data-mdb-ripple="true"
        data-mdb-ripple-color="light"
        class="flex items-center inline-block px-5 py-2.5 bg-secondary text-white font-medium text-xs leading-tight uppercase rounded-xl shadow-md hover:bg-blue-700 hover:shadow-lg focus:bg-blue-700 focus:shadow-lg focus:outline-none focus:ring-0 active:bg-blue-800 active:shadow-lg transition duration-150 ease-in-out"
        @click="$emit('onClick')"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="white"
            class="w-4 h-4 mr-1"
            v-if="icon"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"
            />
        </svg>
        <slot></slot>
    </button>
</template>
