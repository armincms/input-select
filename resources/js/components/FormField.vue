<template>
  <default-field :field="field" :errors="errors">
    <template slot="field">
      <div class="flex">
        <input
            :id="field.attribute"
            :type="inputType"
            class="w-full form-control form-input form-input-bordered"
            :class="errorClasses"
            :placeholder="field.name"
            v-model="value[field.input]"
        />
        <select-control
          :id="field.select"
          :dusk="field.select"
          v-model="value[field.select]"
          class="w-full form-control form-select ml-1"
          :class="errorClasses"
          :options="field.options"
          :disabled="isReadonly"
        >
          <option value="" selected :disabled="!field.nullable">{{
            placeholder
          }}</option>
        </select-control>
      </div>
    </template> 
  </default-field>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'

export default {
  mixins: [FormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'field'],

  methods: {
    /*
     * Set the initial, internal value for the field.
     */
    setInitialValue() {
        this.value = this.field.value
    },

    /**
     * Fill the given FormData object with the field's internal value.
     */
    fill(formData) {  
      formData.append(this.field.attribute, JSON.stringify(this.value)) 
    },

    /**
     * Update the field's internal value.
     */
    handleChange(value) {
        this.value = value
    },
  },

  computed: {
    /**
     * Return the placeholder text for the field.
     */
    placeholder() {
      return this.field.placeholder || this.__('Choose an option')
    },

    inputType() {
      return this.field.type ? this.field.type : 'text';
    },
  },
}
</script>
