// The Vue build version to load with the `import` command (runtime-only or
// standalone) has been set in webpack.dev.conf and webpack.test.conf with an alias.
import Vue from 'vue'
import App from './App'

Vue.config.productionTip = false

/* eslint-disable no-new */
new Vue({
  el: '#email-to-target-messages-widget',
  template: '<App/>',
  components: { App }
})
