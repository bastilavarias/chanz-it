<script>
import { computed } from 'vue';

export default {
    name: 'base-chip',

    props: {
        type: String,
        className: String
    },

    setup(props) {
        const defaultClass = computed(
            () =>
                'px-2 py-1 text-white rounded uppercase font-semibold text-xs flex align-center w-max active:bg-gray-300 transition duration-300 ease'
        );

        const color = computed(() => {
            const combinations = {
                success: 'bg-green-600',
                error: 'bg-red-600'
            };

            return combinations[props.type];
        });

        return { color, defaultClass };
    }
};
</script>

<template>
    <span :class="`${defaultClass} ${color} ${className}`" @click="$emit('onClick')">
        <span class="flex items-center">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="white"
                class="w-6 h-6 mr-1"
                v-if="type === 'success'"
            >
                <path
                    fill-rule="evenodd"
                    d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z"
                    clip-rule="evenodd"
                />
            </svg>

            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="white"
                class="w-6 h-6 mr-1"
                v-if="type === 'error'"
            >
                <path
                    fill-rule="evenodd"
                    d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z"
                    clip-rule="evenodd"
                />
            </svg>
            <slot></slot>
        </span>
    </span>
</template>
