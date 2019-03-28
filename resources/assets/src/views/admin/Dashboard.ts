// eslint-disable-next-line import/no-extraneous-dependencies
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
  const chart = echarts.init(el, void 0, { renderer: 'svg' })
  chart.setOption({
    tooltip: {
      trigger: 'axis',
      axisPointer: {
        type: 'cross',
        label: {
          backgroundColor: '#6a7985',
        },
      },
    },
    dataZoom: [
      { type: 'inside', start: 75 },
      { type: 'slider', start: 75 },
    ],
    legend: {
      data: [],
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
        type: 'category',
        boundaryGap: false,
      },
    ],
    series: [
      {
        name: '',
        type: 'line',
        stack: '',
        areaStyle: {},
        data: [],
      },
      {
        name: '',
        type: 'line',
        stack: '',
        areaStyle: {},
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
        stack: 'total',
        areaStyle: {},
        data: chartData.data[index],
        smooth: true,
        symbol: 'roundRect',
      })
    ),
  })

  return chart
}

const el = document.querySelector<HTMLDivElement>('#chart')
if (el) {
  createChart(el)
}
