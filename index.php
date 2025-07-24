<!DOCTYPE html>
<html lang="zh" class="js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>下载测速</title>
	<link rel="stylesheet" href="css/style.css?v=26">
	<link rel="manifest" href="manifest.json">
	<script type="text/javascript">

	</script>


</head>
<body class="nk-body npc-invest bg-lighter " style="cursor:pointer">
<style>
@font-face {
    font-family:font2;
    src: url(fonts/digit.ttf);
}
.stat {
    width: 100%;
    column-gap: 1rem;
    padding: 1rem 1.5rem;
}
.stat-title {
    white-space: nowrap;
    opacity: .6;
    padding-top:30px;
    padding-bottom:10px;
}
.stat-value {
    white-space: nowrap;
    font-size: 1.8em;
    font-weight: bold;
    font-family:font2;
}


</style>
<div class="container-xl" id="app" style="text-align:center" >

    <div style="font-size:20px;">
        <div class="stat-title">实时下载速率</div>
        <div class="stat-value" >{{speed}}</div><br>
    </div>
    <div style="font-size:20px;">
        <div class="stat-title">已用流量</div>
        <div class="stat-value">{{changeFilesize(waste)+" MB = "+(changeFilesize(waste)/1024).toFixed(2)+" GB"}}</div><br>
    </div>
    <div style="font-size:20px;">
        <div class="stat-title">运行时长与平均速率</div>
        <div class="stat-value">{{secToTime(spend)}}</div><br>
    </div>
    <p style="font-size:20px;color:red;text-align:center">注意：测试为不限时运行，请手动点击停止！</p> 
    <button style="padding:2%;font-size:24px;text-align:center;font-family:font2;"  @click="run">
        {{set.status?'停止':'开始测试'}}
    </button>
            <p style="margin-bottom:1px">本项目基于<a href="https://github.com/uu6/llxhq"> https://github.com/uu6/llxhq </a>二改。开源地址：<a href="https://github.com/CzJam/llxhq">https://github.com/CzJam/llxhq</a>。仅保留核心功能，提高加载速度。</p>
</div></div>

 
    </div>
    <!-- wrap @e -->
<script src="//lib.baomitu.com/jquery/3.6.0/jquery.min.js"></script>
<script src="//lib.baomitu.com/bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
<!--<script src="//lib.baomitu.com/layer/3.5.1/layer.js"></script>-->
<script src="js/nioapp.min.js"></script>
<script src="js/script.js"></script>
<script src="js/common.js?2"></script>


<script src="//lib.baomitu.com/vue/2.6.14/vue.min.js"></script>
<script src="//lib.baomitu.com/axios/0.26.0/axios.min.js"></script>
<script>
var errors = null
new Vue({
    el: '#app',
    data: {
        set: {
            input: 'https://speedtest1.online.sh.cn:8080/download?size=100000000000&nocache=0.8519698186042854',
            output: '',
            infinite: true,
            status: false,
            thread: 32,
        },
        tasks: [],
        speed: '0 MB/s = 0 Mbps',
        spend: 0,
        waste: 0,
        timer: null,
        cancelSource: axios.CancelToken.source()
    },
    watch: {
        async 'set.status'(newVal) {
            if (newVal) {
                this.cancelSource = axios.CancelToken.source()
                this.timer = setInterval(() => {
                    this.speed = this.changeDownloadSpeed(this.tasks.reduce(function (prev, curr) {
                        return prev + curr;
                    }, 0));
                    this.spend++
                }, 1000)
                do {
                    await new Promise(resolve => {
                        let task = []
                        for (let i = 0; i < this.set.thread; i++) {
                            task.push(this.download(Math.random().toString(36).substr(2, 10)))
                        }
                        Promise.all(task).finally(resolve)
                    })
                } while (this.set.status && this.set.infinite)
            } else {
                clearInterval(this.timer)
                this.cancelSource.cancel()
            }
        }
    },
    methods: {
        run() {
            this.set.status = !this.set.status
        },
        download(id) {
            let loaded = 0
            let speed = 0
            let timestamp = new Date().getTime()
            let that = this
            const index = this.tasks.push(speed) - 1
            return axios.request({
                url: this.set.input,
                params: {
                    [id]: id,
                },
                cancelToken: this.cancelSource.token,
                onDownloadProgress: function (progressEvent) {
                    // 处理原生进度事件
                    const now = new Date().getTime();
                    speed = (progressEvent.loaded - loaded) / (now - timestamp) * 1000
                    that.tasks[index] = speed
                    that.waste += progressEvent.loaded - loaded
                    loaded = progressEvent.loaded
                    timestamp = now
                },
            }).catch(e => {
                if (!axios.isCancel(e)) {
                    layer.msg(e.message, {icon:2})
                  //  this.set.status = false
				  this.set.status = true
                }
            }).finally(() => {
                delete that.tasks[index]
            })
        },
        secToTime(s) {
            let t = '';
            if (s > -1) {
                let hour = Math.floor(s / 3600)
                let min = Math.floor(s / 60) % 60
                let sec = s % 60
                if (hour > 0) {
                    if (hour < 10) {
                        t += '0'
                    }
                    t = hour + "h"
                }
                if (hour > 0 || min > 0) {
                    if (min < 10) {
                        t += '0'
                    }
                    t += min + "m"
                }
                if (sec < 10) {
                    t += '0'
                }
                t += sec + 's'
            }
            if(this.spend==0){
                return "00s, 0 MB/s"
            }else{
                return t+", "+(this.changeFilesize(this.waste)/this.spend).toFixed(0)+" MB/s"
            }
            
        },
        changeFilesize: (filesize) => {
            return (parseInt(filesize) / (1024 * 1024)).toFixed(0)
        },

        changeDownloadSpeed(filesize) {
            filesize = this.changeFilesize(filesize);
            return filesize+" MB/s = "+filesize*8+" Mbps"
        }
    },
});


     
</script>

  
<script>document.oncontextmenu = function (event){
if(window.event){
event = window.event;
}try{
var the = event.srcElement;
if (!((the.tagName == "INPUT" && the.type.toLowerCase() == "text") || the.tagName == "TEXTAREA")){
return false;
}
return true;
}catch (e){
return false;
}
}
</script>  
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
    
      gtag('config', 'UA-114909353-1');
    </script>
</body>
</html>
