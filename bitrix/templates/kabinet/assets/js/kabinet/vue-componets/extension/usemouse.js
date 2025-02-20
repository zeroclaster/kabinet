/*  Use...              
				data() {
                    return {                
						...useMouse()
                    }
                },
*/
const useMouse = function () {
  // state encapsulated and managed by the composable
  const x = BX.Vue3.ref(0);
  const y = BX.Vue3.ref(0);

  // a composable can update its managed state over time.
  function update(event) {
	x.value = event.pageX
	y.value = event.pageY
  }

  // a composable can also hook into its owner component's
  // lifecycle to setup and teardown side effects.
  BX.Vue3.onMounted(() => window.addEventListener('mousemove', update))
  BX.Vue3.onUnmounted(() => window.removeEventListener('mousemove', update))

  // expose managed state as return value
  return { x, y }
};
