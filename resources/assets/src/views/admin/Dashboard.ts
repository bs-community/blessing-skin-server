import * as echarts from 'echarts/core'
import { SVGRenderer } from 'echarts/renderers'
import { LineChart } from 'echarts/charts'
import {
  DataZoomComponent,
  GridComponent,
  TitleComponent,
  TooltipComponent,
} from 'echarts/components'
import { get } from '../../scripts/net'

interface ChartData {
  labels: string[]
  xAxis: string[]
  data: number[][]
}

interface SingleChartData {
  label: string
  xAxis: string[]
  data: number[]
}

echarts.use([
  SVGRenderer,
  LineChart,
  DataZoomComponent,
  GridComponent,
  TitleComponent,
  TooltipComponent,
])

async function main() {
  const elUsersRegistration = document.querySelector<HTMLDivElement>(
    '#chart-users-registration',
  )
  const elTexturesUpload = document.querySelector<HTMLDivElement>(
    '#chart-textures-upload',
  )
  if (!elUsersRegistration || !elTexturesUpload) {
    return
  }

  const isDarkMode = document.body.classList.contains('dark-mode')
  const textColor = isDarkMode ? '#fff' : '#000'

  const chartData: ChartData = await get('/admin/chart')
  createLineChart(
    elUsersRegistration,
    isDarkMode ? '#3498db' : '#17a2b8',
    textColor,
    {
      label: chartData.labels[0]!,
      xAxis: chartData.xAxis,
      data: chartData.data[0]!,
    },
  )
  createLineChart(elTexturesUpload, '#6f42c1', textColor, {
    label: chartData.labels[1]!,
    xAxis: chartData.xAxis,
    data: chartData.data[1]!,
  })
}

function createLineChart(
  el: HTMLDivElement,
  color: string,
  textColor: string,
  data: SingleChartData,
) {
  const chart = echarts.init(el)
  chart.setOption({
    title: {
      text: data.label,
      textStyle: {
        color: textColor,
      },
    },
    textStyle: {
      color: textColor,
    },
    tooltip: {
      trigger: 'axis',
    },
    dataZoom: [
      { type: 'inside', start: 75 },
      { type: 'slider', start: 75 },
    ],
    xAxis: [
      {
        type: 'category',
        boundaryGap: false,
        data: data.xAxis,
      },
    ],
    yAxis: [
      {
        type: 'value',
        minInterval: 1,
        boundaryGap: false,
      },
    ],
    series: [
      {
        name: data.label,
        type: 'line',
        itemStyle: {
          color,
        },
        areaStyle: {
          color,
        },
        data: data.data,
        smooth: true,
      },
    ],
  })
}

main()
