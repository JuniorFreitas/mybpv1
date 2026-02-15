<template>
    <div :class="wrapperClass">
        <div class="form-check mb-2">
            <input
                type="checkbox"
                class="form-check-input"
                :disabled="disabled"
                :id="checkboxId"
                :checked="enabled"
                @change="onToggle"
            />
            <label class="form-check-label cursor-pointer fw-bold" :for="checkboxId">
                {{ label }}
            </label>
        </div>

        <div class="input-group input-group-sm">
            <input
                type="date"
                class="form-control"
                :disabled="!enabled || disabled"
                :value="startDate"
                @input="onStartInput"
            />
            <span class="input-group-text bg-light">até</span>
            <input
                type="date"
                class="form-control"
                :disabled="!enabled || disabled"
                :value="endDate"
                @input="onEndInput"
            />
        </div>
    </div>
</template>

<script>
export default {
    name: 'DateRangeFilter',
    props: {
        enabled: {
            type: Boolean,
            default: false,
        },
        startDate: {
            type: String,
            default: '',
        },
        endDate: {
            type: String,
            default: '',
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        label: {
            type: String,
            default: 'Filtrar por Período',
        },
        wrapperClass: {
            type: String,
            default: 'col-12 col-md-6 col-lg-4 mb-3',
        },
        idSuffix: {
            type: String,
            default: '',
        },
    },
    computed: {
        checkboxId() {
            return this.idSuffix ? `filtroIntervalo_${this.idSuffix}` : 'filtroIntervalo';
        },
    },
    data() {
        return {
            isAdjusting: false,
        };
    },
    watch: {
        startDate() {
            this.ensureAdjusted();
        },
        endDate() {
            this.ensureAdjusted();
        },
    },
    methods: {
        onToggle(event) {
            const checked = event.target.checked;
            this.$emit('update:enabled', checked);
            if (!checked) {
                this.$emit('update:startDate', '');
                this.$emit('update:endDate', '');
                return;
            }
            const today = this.getToday();
            if (!this.startDate) {
                this.$emit('update:startDate', today);
            }
            if (!this.endDate) {
                this.$emit('update:endDate', today);
            }
        },
        onStartInput(event) {
            const value = event.target.value;
            this.$emit('update:startDate', value);
            this.ensureOrder(value, this.endDate);
        },
        onEndInput(event) {
            const value = event.target.value;
            this.$emit('update:endDate', value);
            this.ensureOrder(this.startDate, value);
        },
        isAfter(start, end) {
            return new Date(start) > new Date(end);
        },
        ensureOrder(start, end) {
            if (!start || !end) {
                return;
            }
            if (this.isAfter(start, end)) {
                this.notifyInvalid();
                this.$emit('update:startDate', end);
                this.$emit('update:endDate', start);
            }
        },
        ensureAdjusted() {
            if (this.isAdjusting) {
                return;
            }
            const start = this.startDate;
            const end = this.endDate;
            if (!start || !end) {
                return;
            }
            if (this.isAfter(start, end)) {
                this.isAdjusting = true;
                this.notifyInvalid();
                this.$emit('update:startDate', end);
                this.$emit('update:endDate', start);
                this.$nextTick(() => {
                    this.isAdjusting = false;
                });
            }
        },
        notifyInvalid() {
            const msg = 'A data inicial não pode ser maior que a data final. Datas ajustadas automaticamente.';
            if (typeof window !== 'undefined' && typeof window.mostraErro === 'function') {
                window.mostraErro('Erro', msg);
                return;
            }
            if (typeof mostraErro === 'function') {
                mostraErro('Erro', msg);
                return;
            }
            if (typeof window !== 'undefined' && window.toastr && typeof window.toastr.error === 'function') {
                window.toastr.error(msg);
            }
        },
        getToday() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        },
    },
};
</script>
<style scoped>
.input-group-text {
    padding: inherit;
    border-radius: 0;
}
</style>
