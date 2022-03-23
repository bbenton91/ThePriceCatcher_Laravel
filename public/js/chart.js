
function drawChart(data, labels){
    // if(data.length > 10){
    //     data = data.filter((el, index) => index%3 != 0);
    //     labels = labels.filter((el, index) => index%3 != 0);
    // }

    var ctx = document.getElementById('myChart');
    console.log(labels);
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Sale Price',
                data: data,
                backgroundColor: [
                    'rgba(0, 0, 255, 0.2)'
                ],
                borderColor: [
                    'rgba(0, 50, 255, 1)'
                ],
                borderWidth: 1,
                pointBackgroundColor: Array(data.length).fill("red")
            }]
        },
        options: {
            scales: {
                xAxes: [{
                    ticks: {
                      fontColor: "black"
                    },
                    gridLines: {
                        color: 'rgba(0, 0, 0, 0.1)' // makes grid lines from y axis red
                    }
                  }],
                yAxes: [{
                    ticks: {
                        fontColor: "black"
                        // beginAtZero: true
                    },
                    gridLines: {
                        color: 'rgba(0, 0, 0, 0.1)' // makes grid lines from y axis red
                    }
                }]
            }
        }
    });
}
