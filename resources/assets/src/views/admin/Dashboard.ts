import 'zrender/lib/svg/svg'
import echarts from 'echarts/lib/echarts'
import 'echarts/lib/chart/line'
import 'echarts/lib/component/dataZoom'
import 'echarts/lib/component/legend'
import 'echarts/lib/component/tooltip'
import { get } from '../../scripts/net'

interface ChartData {
  labels: string[]
  xAxis: string[]
  data: number[][]
}

async function createChart(el: HTMLDivElement) {
  const isDarkMode = document.body.classList.contains('dark-mode')
  const textColor = isDarkMode ? '#fff' : '#000'

  const chart = echarts.init(el, void 0, { renderer: 'svg' })
  chart.setOption({
    textStyle: {
      color: textColor,
    },
    tooltip: {
      trigger: 'axis',
      axisPointer: {
        type: 'cross',
      },
    },
    dataZoom: [
      { type: 'inside', start: 75 },
      { type: 'slider', start: 75 },
    ],
    legend: {
      data: [],
      textStyle: {
        color: textColor,
      },
    },
    xAxis: [
      {
        type: 'category',
        boundaryGap: false,
        data: [],
      },
    ],
    yAxis: [
      {
        type: 'value',
        minInterval: 1,
        boundaryGap: false,
        position: 'left',
        splitLine: {
          show: false,
        },
        axisPointer: {
          label: {
            precision: 0,
          },
        },
      },
      {
        type: 'value',
        minInterval: 1,
        boundaryGap: false,
        position: 'right',
        splitLine: {
          show: false,
        },
        axisPointer: {
          label: {
            precision: 0,
          },
        },
      },
    ],
    series: [
      {
        name: '',
        type: 'line',
        stack: '',
        itemStyle: {
          color: '#B5F079',
        },
        areaStyle: {
          color: '#B5F079',
        },
        data: [],
      },
      {
        name: '',
        type: 'line',
        stack: '',
        itemStyle: {
          color: '#6FADEB',
        },
        areaStyle: {
          color: '#6FADEB',
        },
        data: [],
      },
    ],
  })

  const chartData: ChartData = await get('/admin/chart')
  chart.setOption({
    legend: {
      data: chartData.labels,
    },
    xAxis: [
      {
        type: 'category',
        boundaryGap: false,
        data: chartData.xAxis,
        axisLabel: { margin: 16 },
      },
    ],
    series: chartData.labels.map(
      (label: string, index: number): echarts.EChartOption.SeriesLine => ({
        name: label,
        type: 'line',
        data: chartData.data[index],
        smooth: true,
        symbol: 'circle',
        yAxisIndex: index,
      }),
    ),
  })

  return chart
}

const el = document.querySelector<HTMLDivElement>('#chart')
if (el) {
  createChart(el)
}
