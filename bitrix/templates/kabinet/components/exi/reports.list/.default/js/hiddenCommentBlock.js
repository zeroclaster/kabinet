const s_ = BX.Vue3.ref([]);
const hiddenCommentBlock = {
    isShow(runner) {
        const Store = messageStore;

        if (typeof s_.value[runner.ID] == "undefined") s_.value[runner.ID] = 0;
        if (Store.datamessage[runner.ID].length > 0 ) s_.value[runner.ID] = 1;
        return s_.value[runner.ID];
    },
    mclick(runner) {
        s_.value[runner.ID] = 1;
    }
}