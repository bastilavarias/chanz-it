<script>
import { computed, reactive, ref, watch } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import BaseInput from '@/components/BaseInput.vue';
import BaseChip from '@/components/BaseChip.vue';
import utilityHelper from '@/helpers/utility.js';
import CustomLoadingSpinner from '@/components/CustomLoadingSpinner.vue';
import CustomButton from '@/components/CustomButton.vue';

export default {
    components: { CustomButton, CustomLoadingSpinner, BaseChip, BaseInput },

    setup() {
        const mode = ref('words-to-numbers');
        // const mode = ref('numbers-to-words');
        const words = ref('');
        const numbers = ref(0);
        const parsingResult = ref('');
        const pesoToConvert = ref(0);
        const toUSDConversionResult = ref('');
        const hasWordsTypoError = ref(false);
        const hasInvalidWordsError = ref(false);
        const hasInvalidNumbersError = ref(false);
        const hasUSDConversionError = ref(false);
        const isUSDConversionSuccess = ref(false);
        const errorMessage = ref('');
        const suggestion = ref('');
        const isParseToNumbersStart = ref(false);
        const isParseToWordsStart = ref(false);
        const isConvertToUSDStart = ref(false);
        const { debounce, formatMoney } = utilityHelper;

        const formattedParseToNumberResult = computed(() =>
            formatMoney(parsingResult.value, 'PHP')
        );

        watch(
            words,
            debounce(async value => {
                if (value) {
                    await parseToNumber();
                }
            }, 800)
        );

        watch(
            numbers,
            debounce(async value => {
                if (value) {
                    await parseToWords();
                }
            }, 800)
        );

        watch(parsingResult, async value => {
            if (value) {
                pesoToConvert.value =
                    mode.value === 'words-to-numbers' ? value : numbers.value;
                await convertToUSD();
            }
        });

        watch(mode, () => {
            onResetData();
        });

        const parseToNumber = async () => {
            try {
                const payload = { words: words.value.trim().toUpperCase() };
                hasInvalidWordsError.value = false;
                hasWordsTypoError.value = false;
                hasUSDConversionError.value = false;
                errorMessage.value = null;
                isParseToNumbersStart.value = true;
                const response = await window.axios.post('api/parse/number', payload);
                const { data } = response.data;
                parsingResult.value = data;
                isParseToNumbersStart.value = false;
            } catch (_error) {
                const { message, data } = _error.response.data;
                errorMessage.value = message.toUpperCase() || words;
                const errorType = data.type;
                if (errorType === 'typo_words') {
                    suggestion.value = data.suggestion;
                    hasWordsTypoError.value = true;
                } else {
                    hasInvalidWordsError.value = true;
                }
                isParseToNumbersStart.value = false;
            }
        };

        const parseToWords = async () => {
            try {
                const payload = { numbers: numbers.value };
                hasInvalidWordsError.value = false;
                hasUSDConversionError.value = false;
                errorMessage.value = null;
                isParseToWordsStart.value = true;
                const response = await window.axios.post('api/parse/word', payload);
                const { data } = response.data;
                parsingResult.value = data.toUpperCase();
                isParseToWordsStart.value = false;
            } catch (_error) {
                const { message } = _error.response.data;
                errorMessage.value = message.toUpperCase() || words;
                hasInvalidWordsError.value = true;
                isParseToWordsStart.value = false;
            }
        };

        const convertToUSD = async () => {
            try {
                const payload = { peso: pesoToConvert.value };
                isConvertToUSDStart.value = true;
                const response = await window.axios.post('api/conversion/usd', payload);
                const { data } = response.data;
                toUSDConversionResult.value = `${formatMoney(data, 'USD')}`;
                isConvertToUSDStart.value = false;
                isUSDConversionSuccess.value = true;
            } catch (_error) {
                const { message } = _error.response.data;
                errorMessage.value = message;
                hasUSDConversionError.value = true;
                isConvertToUSDStart.value = false;
            }
        };

        const onResetData = () => {
            words.value = '';
            numbers.value = 0;
            parsingResult.value = '';
            toUSDConversionResult.value = '';
            hasWordsTypoError.value = false;
            hasInvalidWordsError.value = false;
            hasInvalidNumbersError.value = false;
            hasUSDConversionError.value = false;
            isUSDConversionSuccess.value = false;
            errorMessage.value = '';
            suggestion.value = '';
            isParseToNumbersStart.value = false;
            isConvertToUSDStart.value = false;
        };

        const onChangeMode = () => {
            if (mode.value === 'words-to-numbers') {
                mode.value = 'numbers-to-words';
                return;
            }
            mode.value = 'words-to-numbers';
        };

        const onSuggestionClick = async () => {
            words.value = suggestion.value;
            errorMessage.value = null;
            hasWordsTypoError.value = false;
            await parseToNumber();
        };

        return {
            words,
            numbers,
            hasWordsTypoError,
            hasInvalidWordsError,
            hasInvalidNumbersError,
            hasUSDConversionError,
            isParseToNumbersStart,
            isParseToWordsStart,
            isConvertToUSDStart,
            errorMessage,
            onSuggestionClick,
            onChangeMode,
            parsingResult,
            toUSDConversionResult,
            formatMoney,
            formattedParseToNumberResult,
            isUSDConversionSuccess,
            pesoToConvert,
            mode,
            onResetData
        };
    }
};
</script>

<template>
    <div class="w-screen h-screen bg-primary">
        <div class="container mx-auto pt-8">
            <div class="text-white mb-16 flex items-center space-x-3">
                <h1 class="text-2xl text-white text-gray-400 font-bold">
                    {{
                        mode === 'words-to-numbers'
                            ? 'Words to Numbers'
                            : 'Numbers to Words'
                    }}
                </h1>
                <custom-button icon @onClick="onChangeMode">Change Mode</custom-button>
            </div>

            <div class="space-y-16 divide-y divide-secondary">
                <div
                    class="block space-y-8 md:space-y-0 md:flex md:items-center md:space-x-5"
                >
                    <template v-if="mode === 'words-to-numbers'">
                        <div class="md:w-2/4 md:flex md:flex-col">
                            <div class="flex flex-col space-y-5">
                                <div class="flex items-center space-x-2">
                                    <h2 class="text-xl text-white font-semibold">
                                        Words
                                    </h2>
                                    <custom-loading-spinner
                                        v-if="isParseToNumbersStart"
                                    ></custom-loading-spinner>
                                    <base-chip
                                        @onClick="onSuggestionClick"
                                        type="error"
                                        class-name="cursor-pointer"
                                        v-if="!isParseToNumbersStart && hasWordsTypoError"
                                        >{{ errorMessage }}</base-chip
                                    >
                                    <base-chip
                                        type="error"
                                        v-if="
                                            !isParseToNumbersStart && hasInvalidWordsError
                                        "
                                        >{{ errorMessage }}</base-chip
                                    >
                                </div>
                                <base-input
                                    placeholder="Type your words here..."
                                    v-model="words"
                                ></base-input>
                            </div>
                        </div>
                        <div class="text-center md:pt-10">
                            <span class="text-white font-semibold">to</span>
                        </div>
                        <div class="md:w-2/4 md:flex md:flex-col">
                            <div class="flex flex-col space-y-5">
                                <div class="flex items-center space-x-2">
                                    <h2 class="text-xl text-white font-semibold">
                                        (₱) Numbers
                                    </h2>
                                </div>
                                <base-input
                                    readonly
                                    :placeholder="formatMoney(0, 'PHP')"
                                    v-model="formattedParseToNumberResult"
                                ></base-input>
                            </div>
                        </div>
                    </template>

                    <template v-if="mode === 'numbers-to-words'">
                        <div class="md:w-2/4 md:flex md:flex-col">
                            <div class="flex flex-col space-y-5">
                                <div class="flex items-center space-x-2">
                                    <h2 class="text-xl text-white font-semibold">
                                        (₱) Numbers
                                    </h2>
                                    <custom-loading-spinner
                                        v-if="isParseToWordsStart"
                                    ></custom-loading-spinner>
                                    <base-chip
                                        type="error"
                                        v-if="
                                            !isParseToWordsStart && hasInvalidWordsError
                                        "
                                        >{{ errorMessage }}</base-chip
                                    >
                                </div>
                                <base-input
                                    placeholder="Type your numbers here..."
                                    type="number"
                                    v-model="numbers"
                                ></base-input>
                            </div>
                        </div>
                        <div class="text-center md:pt-10">
                            <span class="text-white font-semibold">to</span>
                        </div>
                        <div class="md:w-2/4 md:flex md:flex-col">
                            <div class="flex flex-col space-y-5">
                                <div class="flex items-center space-x-2">
                                    <h2 class="text-xl text-white font-semibold">
                                        Words
                                    </h2>
                                </div>
                                <base-input
                                    readonly
                                    placeholder="Parsed words"
                                    v-model="parsingResult"
                                ></base-input>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex flex-col space-y-5 pt-5">
                    <div class="flex items-center space-x-2">
                        <h2 class="text-xl text-white font-semibold">
                            PHP to USD Conversion
                        </h2>
                        <custom-loading-spinner
                            v-if="isConvertToUSDStart"
                        ></custom-loading-spinner>
                        <base-chip
                            type="success"
                            v-if="!isConvertToUSDStart && isUSDConversionSuccess"
                            >{{ formatMoney(pesoToConvert, 'PHP') }} was successfully
                            converted to USD.</base-chip
                        >
                        <base-chip
                            type="error"
                            v-if="!isConvertToUSDStart && hasUSDConversionError"
                            >{{ errorMessage }}</base-chip
                        >
                    </div>
                    <base-input
                        readonly
                        :placeholder="formatMoney(0, 'USD')"
                        v-model="toUSDConversionResult"
                    ></base-input>
                    <div class="flex justify-center py-5" v-if="parsingResult">
                        <custom-button @onClick="onResetData">Reset</custom-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
@tailwind base;
@tailwind components;
@tailwind utilities;

input[type='number']::-webkit-outer-spin-button,
input[type='number']::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type='number'] {
    -moz-appearance: textfield;
}
</style>
