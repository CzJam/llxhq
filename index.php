<!DOCTYPE html>
<html lang="zh" class="js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>下载测速</title>
    <link rel="stylesheet" href="css/dashlite.css">
	<link rel="stylesheet" href="css/style.css?v=202220626">
	<link rel="manifest" href="manifest.json">
	<script type="text/javascript">
// 	if (navigator.serviceWorker != null) {
// 		navigator.serviceWorker.register('sw-pwa.js?20220628')
// 		.then(function(registration) {
// 			console.log('Registered events at scope: ', registration.scope);
// 		});
// 	}
	</script>


</head>
<body class="nk-body npc-invest bg-lighter " style="cursor:pointer">
<style>
.stat {
    width: 100%;
    column-gap: 1rem;
    padding: 1rem 1.5rem;
}
.stat-title {
    white-space: nowrap;
    opacity: .6;
}
.stat-value {
    white-space: nowrap;
    grid-column-start: 1;
    font-size: 1.8rem;
    font-weight: 700;
    line-height: 2.5rem
}
</style>
<div class="container-xl" id="app">
<div class="col-sm-12 col-md-10 col-xl-8 center-block">
    <div class="card card-preview">
        <div class="card-inner mt-3">
           
            
			
            <div class="row mt-4">
                <div class="col-sm-12 col-md-4 border stat">
                    <div class="text-info"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-15 w-15 float-right pt-3"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg></div>
                    <div class="stat-title">下载速率</div>
                    <div class="stat-value text-info">{{speed}}</div>
                </div>
                <div class="col-sm-12 col-md-4 border stat">
                    <div class="text-dark"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-15 w-15 float-right pt-3"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg></div>
                    <div class="stat-title">已使用流量</div>
                    <div class="stat-value">{{changeFilesize(waste)}}</div>
                </div>
                
                <div class="col-sm-12 col-md-4 border stat">
                    <div class="text-dark"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-15 w-15 float-right pt-3"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                    <div class="stat-title">运行时长</div>
                    <div class="stat-value">{{secToTime(spend)}}</div>
                </div>
                </div> 
                <div class="form-group" style="margin-top:20px">
                <label class="form-label" style="font-size:26px"><b>线程数<a style="font-size:14px">（若设备性能不足，过量的线程会导致浏览器卡顿，影响测试准确性）</a></b></label>
                <div class="form-control-wrap number-spinner-wrap">
                    <button class="btn btn-icon btn-outline-primary number-spinner-btn number-minus" @click="if(set.thread>1)set.thread--"><em class="icon ni ni-minus"></em></button>
                    <input type="number" style="font-size:18px;font-weight:Bold" readonly="readonly" class="form-control number-spinner" min="1" max="16" v-model="set.thread">
                    <button class="btn btn-icon btn-outline-primary number-spinner-btn number-plus"><em class="icon ni ni-plus" @click="if(set.thread<16)set.thread++"></em></button>
                </div>
            </div>
                <button style="margin:20px 0;padding:20px;font-size:24px" class="btn btn-dim btn-outline-secondary btn-block card-link" @click="run">
                {{set.status?'停止':'开始'}}
            </button>
            <p>本项目基于<a href="https://github.com/uu6/llxhq"> https://github.com/uu6/llxhq </a>二改(开源地址：<a href="https://github.com/CzJam/llxhq">https://github.com/CzJam/llxhq</a>)。仅保留核心功能，提高加载和使用效率。
        </div>
    </div>
</div>
</div>
        </div>
        <!-- content @d -->

<!-- footer @e -->
    </div>
    <!-- wrap @e -->
<script src="//cdn.staticfile.org/jquery/3.6.0/jquery.min.js"></script>
<script src="//cdn.staticfile.org/bootstrap/4.6.1/js/bootstrap.bundle.min.js"></script>
<script src="//cdn.staticfile.org/layer/3.5.1/layer.js"></script>
<script src="js/nioapp.min.js"></script>
<script src="js/script.js"></script>
<script src="js/common.js"></script>


<script src="//cdn.staticfile.org/vue/2.6.14/vue.min.js"></script>
<script src="//cdn.staticfile.org/axios/0.26.0/axios.min.js"></script>
<script>
var errors = null
new Vue({
    el: '#app',
    data: {
        set: {
            input: 'https://cdn-pcagamecenter.dsgame.iqiyi.com/upload/2/pca/20230228111207/1/iqiyigame13.4.1.0.exe',
            output: '',
            infinite: true,
            status: false,
            thread: 4,
        },
        tasks: [],
        speed: '0.00B/s',
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
            return t
        },
        changeFilesize: (filesize) => {
            filesize = parseInt(filesize);
            let size = "";
            if (filesize === 0) {
                size = "0.00 B"
            } else if (filesize < 1024) { //小于1KB，则转化成B
                size = filesize.toFixed(2) + " B"
            } else if (filesize < 1024 * 1024) { //小于1MB，则转化成KB
                size = (filesize / 1024).toFixed(2) + " KB"
            } else if (filesize < 1024 * 1024 * 1024) { //小于1GB，则转化成MB
                size = (filesize / (1024 * 1024)).toFixed(2) + " MB"
            } else { //其他转化成GB
                size = (filesize / (1024 * 1024 * 1024)).toFixed(2) + " GB"
            }
            return size;
        },
        changeDownloadSpeed(filesize) {
            filesize = this.changeFilesize(filesize);
            return filesize.replace(/\s([K|M|G|B]*)B{0,1}/, '$1/s')
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
