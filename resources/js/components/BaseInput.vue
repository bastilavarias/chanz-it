<script>
export default {
    name: 'BaseInput',

    props: {
        modelValue: [String, Number, null],
        readonly: Boolean,
        placeholder: String
    },

    emits: ['update:modelValue'],

    methods: {
        onInputChange(event) {
            this.$emit('update:modelValue', event.target.value.toUpperCase());
        }
    }
};
</script>

<template>
    <input
        autofocus
        class="w-full bg-secondary rounded-lg p-5 font-semibold text-white focus:outline-none"
        :value="modelValue"
        :readonly="readonly"
        :placeholder="placeholder"
        @input="onInputChange"
    />
</template>
