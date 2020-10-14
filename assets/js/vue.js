import Vue from 'vue';

var vue = new Vue({
    el: "#plateau",
    delimiters : ['{','}'],
    data: {
        colorSelection: "none",
        hello: "hello vue",
        arrayTerrainSelection:[

        ],
        arrayMainSelection : [

        ]
    },
    methods: {
        selection: function (id) {
            if (this.arrayTerrainSelection.indexOf(id) == -1) {
                if (this.arrayTerrainSelection.length == 2) {
                    console.log('Vous ne pouvez pas séléctionenr plus de deux cartes')
                }
                else {
                    this.arrayTerrainSelection.push(
                        id
                    )
                    console.log("Ajout de " +id)
                }


            }
            else {
                console.log(this.arrayTerrainSelection.indexOf(id))
                console.log("Retrait de " + id)
                this.arrayTerrainSelection.splice(0,id)
                console.log(this.arrayTerrainSelection.indexOf(id))
            }
        }
    }
});
