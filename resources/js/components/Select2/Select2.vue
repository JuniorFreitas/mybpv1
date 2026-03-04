<template>
    <div>
        <select :id="id" :name="name" :disabled="disabled" :required="required"></select>
    </div>
</template>

<script>
import $ from 'jquery'
import './depends/select2.full'
import './depends/select2.min.css'
import './depends/custom.css'

export default {
    name: 'Select2',
    emits: ['update:modelValue', 'change', 'select', 'closing', 'close', 'opening', 'open', 'clearing', 'clear'],
    data() {
        return {
            select2: null
        }
    },
    props: {
        id: {
            type: String,
            default: ''
        },
        name: {
            type: String,
            default: ''
        },
        placeholder: {
            type: String,
            default: ''
        },
        options: {
            type: Array,
            default: () => []
        },
        disabled: {
            type: Boolean,
            default: false
        },
        required: {
            type: Boolean,
            default: false
        },
        settings: {
            type: Object,
            default: () => {}
        },
        modelValue: null,
        value: null
    },
    watch: {
        options(val) {
            this.setOption(val)
        },
        modelValue(val) {
            this.setValue(val)
        },
        value(val) {
            this.setValue(val)
        }
    },
    methods: {
        setOption(val = []) {
            this.select2.empty()
            this.select2.select2({
                placeholder: this.placeholder,
                ...this.settings,
                data: val
            })
            this.setValue(this.modelValue ?? this.value)
        },
        setValue(val) {
            if (val instanceof Array) {
                this.select2.val([...val])
            } else {
                this.select2.val([val])
            }
            this.select2.trigger('change')
        }
    },
    mounted() {
        this.select2 = $(this.$el)
            .find('select')
            .select2({
                placeholder: this.placeholder,
                ...this.settings,
                data: this.options
            })
            .on('select2:select select2:unselect', (ev) => {
                const value = this.select2.val()
                this.$emit('update:modelValue', value)
                this.$emit('change', value)
                this.$emit('select', ev['params']['data'])
            })
            .on('select2:closing', (ev) => {
                this.$emit('closing', ev)
            })
            .on('select2:close', (ev) => {
                this.$emit('close', ev)
            })
            .on('select2:opening', (ev) => {
                this.$emit('opening', ev)
            })
            .on('select2:open', (ev) => {
                document.querySelector('.select2-search__field').focus()
                this.$emit('open', ev)
            })
            .on('select2:clearing', (ev) => {
                this.$emit('clearing', ev)
            })
            .on('select2:clear', (ev) => {
                this.$emit('clear', ev)
            })
        this.setValue(this.modelValue ?? this.value)
    },
    beforeUnmount() {
        this.select2.select2('destroy')
    }
}
</script>
