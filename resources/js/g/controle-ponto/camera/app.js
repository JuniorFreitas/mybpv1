import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'

const app = createApp({
    components: {},
    data() {
        return {
            URL_ADMIN,
            listaCameras: [],
            video: null,
            cameraSelecionada: null,
            canvas: null,
            achou: null,
            precisao: 0
        }
    },
    mounted() {
        Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(`${URL_ADMIN}/../js/g/controle-ponto/camera/models`),
            faceapi.nets.faceLandmark68Net.loadFromUri(`${URL_ADMIN}/../js/g/controle-ponto/camera/models`),
            faceapi.nets.faceRecognitionNet.loadFromUri(`${URL_ADMIN}/../js/g/controle-ponto/camera/models`),
            //faceapi.nets.faceExpressionNet.loadFromUri(`${URL_ADMIN}/../js/g/controle-ponto/camera/models`),
            //faceapi.nets.ageGenderNet.loadFromUri(`${URL_ADMIN}/../js/g/controle-ponto/camera/models`),
            faceapi.nets.ssdMobilenetv1.loadFromUri(`${URL_ADMIN}/../js/g/controle-ponto/camera/models`)
        ]).then(this.startVideo)
    },
    computed: {},
    methods: {
        loadLabels() {
            const labels = ['Felipe Augusto']
            return Promise.all(
                labels.map(async (label) => {
                    const descriptions = []
                    for (let i = 1; i <= 5; i++) {
                        const img = await faceapi.fetchImage(`${URL_ADMIN}/../js/g/controle-ponto/camera/labels/foto${i}.jpg`)
                        const detections = await faceapi.detectSingleFace(img).withFaceLandmarks().withFaceDescriptor()
                        descriptions.push(detections.descriptor)
                    }
                    return new faceapi.LabeledFaceDescriptors(label, descriptions)
                })
            )
        },
        mudaCamera() {
            if (this.cameraSelecionada != null) {
                navigator.getUserMedia(
                    {
                        video: { deviceId: this.cameraSelecionada.deviceId }
                    },
                    (stream) => (this.video.srcObject = stream),
                    (error) => alert(error)
                )
                //evento
                this.video.addEventListener('play', async () => {
                    const canvas = faceapi.createCanvasFromMedia(this.video)

                    const labels = await this.loadLabels()
                    const canvasSize = {
                        width: this.video.width,
                        height: this.video.height
                    }
                    faceapi.matchDimensions(canvas, canvasSize)
                    //document.getElementById('areaVideo').prepend(canvas);
                    //canvas.srcObject = this.video.srcObject;

                    setInterval(async () => {
                        const detections = await faceapi
                            .detectAllFaces(this.video, new faceapi.TinyFaceDetectorOptions())
                            .withFaceLandmarks()
                            //.withFaceExpressions()
                            //.withAgeAndGender()
                            .withFaceDescriptors()

                        const resizedDetections = faceapi.resizeResults(detections, canvasSize)
                        const faceMatcher = new faceapi.FaceMatcher(labels, 0.6)
                        const results = resizedDetections.map((d) => faceMatcher.findBestMatch(d.descriptor))
                        this.achou = !!_.find(results, { label: 'Felipe Augusto' })
                        canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height)

                        //faceapi.draw.drawDetections(canvas, resizedDetections);
                        //faceapi.draw.drawFaceLandmarks(canvas, resizedDetections);
                        //faceapi.draw.drawFaceExpressions(canvas, resizedDetections);

                        /*resizedDetections.forEach(detection => {
                            const { age, gender, genderProbability } = detection;
                            new faceapi.draw.DrawTextField([
                                `${parseInt(age, 10)} years`,
                                `${gender} (${parseInt(genderProbability * 100, 10)})`
                            ], detection.detection.box.topRight).draw(canvas)
                        });*/

                        results.forEach((result, index) => {
                            /*const box = resizedDetections[index].detection.box*/
                            const { label, distance } = result
                            /*new faceapi.draw.DrawTextField([
                                `${label} (${parseInt(distance * 100, 10)}%)`
                            ], box.bottomRight).draw(canvas)*/
                            this.precisao = parseInt(distance * 100, 10)
                        })
                    }, 100)
                })
            } else {
                this.video.srcObject = null
            }
        },
        startVideo() {
            this.video = document.getElementById('video')
            this.canvas = document.getElementById('canvas_faceapi')

            function hasGetUserMedia() {
                return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia)
            }
            if (hasGetUserMedia()) {
                // Good to go!
            } else {
                alert('getUserMedia() is not supported by your browser')
            }

            navigator.mediaDevices.enumerateDevices().then((devices) => {
                if (Array.isArray(devices)) {
                    this.listaCameras = devices.filter((device) => device.kind === 'videoinput')
                }
                if (this.listaCameras.length === 0) {
                    alert('Seu dispositivo não tem camera')
                }
            })
        }
    }
})

registerGlobals(app)
app.mount('#app')
