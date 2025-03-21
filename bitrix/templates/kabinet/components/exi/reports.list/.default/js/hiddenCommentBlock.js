const hiddenCommentBlock_ = function () {

    const s_ = BX.Vue3.ref([]);

    const hiddenCommentBlock = {
        isShow(runner) {
            if (typeof s_.value[runner.ID] == "undefined") s_.value[runner.ID] = 0;
            return s_.value[runner.ID];
        },
        mclick(runner) {
            s_.value[runner.ID] = 1;
        }
    }

    return hiddenCommentBlock;
}