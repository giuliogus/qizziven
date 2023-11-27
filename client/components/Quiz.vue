<script setup>

import Echo from "laravel-echo"
import Pusher from "pusher-js"
import moment from "moment"

import {useToast} from 'vue-toast-notification'
import 'vue-toast-notification/dist/theme-sugar.css'

const $toast = useToast()

const config = useRuntimeConfig()

const { user, token } = useAuth()

var gamesPerMatch = 5;
var countdownSeconds = 30;
var chanceSeconds = 10;

var youHaveToAnswer = ref(false)
var youCanReserve = ref(true)
var yourAnswer = ref('')
var uiStatus = ref('no_game')
var countdownInterval = ref(null)
var adminsList = ref([])
var usersList = ref([])
var questionText = ref('')
var scoreboardData = ref(null)
var matchData = ref(null)
var gameData = ref(null)
var uiInitialized = ref(false)
var isConnected = ref(false)
var waitingSeconds = ref(0)

window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: "pusher",
    cluster: config.public.pusherAppCluster,
    encrypted: true,
    key: config.public.pusherAppKey,
    disableStats: true,
    forceTLS: config.public.pusherScheme == 'https',
    wsHost: config.public.pusherHost,
    wsPort: config.public.pusherPort,
    authEndpoint: config.public.apiEndpoint + config.public.apiPath + '/broadcasting/auth',
    auth: {
        headers: {
            Authorization: "Bearer " + token
        }
    }
});

function importFetchedData(response)
{
    let data = response.game;
    if(data !== null) {
        data.created_at = moment(data.created_at)
        if(data.answers.length > 0) {
            data.answers.forEach((a, i) => {
                data.answers[i].updated_at = moment(data.answers[i].updated_at)
            })
        }
    }
    scoreboardData.value = response.scoreboard
    matchData.value = response.match
    gameData.value = data
    uiStatus.value = response.status
}

function updateScoreboard()
{
    if(scoreboardData.value !== null) {
        scoreboardData.value.forEach((winner) => {
            usersList.value.forEach((u) => {
                if(u.id == winner.id) {
                    u.matches = winner.wins
                }
            })
        })
    } 
    if(matchData.value !== null) {
        matchData.value.scoreboard.forEach((winner) => {
            usersList.value.forEach((u) => {
                if(u.id == winner.id) {
                    u.games = winner.games
                }
            })
        })
    } else {
        usersList.value.forEach((u) => {
            u.games = 0
        })
    }
}

async function checkGame() {
    const initData = await $fetch(config.public.apiEndpoint + config.public.apiPath + '/game', {
        method: 'GET',
        headers: {
            Authorization: "Bearer " + token
        },
    })
    importFetchedData(initData)
    updateGameStatus()
}

function updateGameStatus() {

    if(gameData.value.blocked.length >= usersList.value.length && usersList.value.length > 0) {
        uiStatus.value = 'game_over'
        endGame()
    }
    
    youCanReserve = !user.admin && !gameData.value.blocked.includes(user.id)
    youHaveToAnswer.value = uiStatus.value == 'answer_reserved' && gameData.value.answers.length > 0 && gameData.value.answers[0].author.id == user.id

    if(uiStatus.value == 'answer_given') {
        youHaveToAnswer.value = false
    }

    if(uiStatus.value == 'game_started') {
        startCountdown()    
    } else {
        clearInterval(countdownInterval.value)
    }

    updateScoreboard()

    if(uiStatus.value == 'match_winned') {
        usersList.value.forEach((u) => {
            u.games = 0
        })
    }

    uiInitialized.value = true
}

function askQuestion() {
    if(uiStatus.value != 'game_started') {
        if(questionText.value != '') {
            $fetch(config.public.apiEndpoint + config.public.apiPath + '/game', {
                method: 'POST',
                headers: {
                    Authorization: "Bearer " + token
                },
                body: {
                    question: questionText.value
                }
            })
            questionText.value = ''
        } else {
            $toast.error("Per favore, scrivi qualcosa...");
        }
    }
}

function endGame() {
    if(uiStatus.value != 'game_over') {
        $fetch(config.public.apiEndpoint + config.public.apiPath + '/game', {
            method: 'DELETE',
            headers: {
                Authorization: "Bearer " + token
            },
            body: {
                winner: null
            }
        })
    }
}

function restartGame()
{
    $fetch(config.public.apiEndpoint + config.public.apiPath + '/game/close', {
        method: 'PUT',
        headers: {
            Authorization: "Bearer " + token
        },
    })
}

function startCountdown() {
    console.log('countdownInterval', countdownInterval.value)
        if(gameData.value.answers.length == 0) {
            console.log((parseInt(gameData.value.created_at.format('X')) + countdownSeconds) - parseInt(moment().format('X')))
            waitingSeconds.value = (parseInt(gameData.value.created_at.format('X')) + countdownSeconds) - parseInt(moment().format('X'))
        } else {
            waitingSeconds.value = (parseInt(gameData.value.answers[0].updated_at.format('X')) + chanceSeconds) - parseInt(moment().format('X'))
            console.log((parseInt(gameData.value.answers[0].updated_at.format('X')) + chanceSeconds) - parseInt(moment().format('X')))
        }
        countdownInterval.value = setInterval(() => {
            if(waitingSeconds.value <= 0) {
                    clearInterval(countdownInterval.value)
                    endGame()
            } else {
                waitingSeconds.value--;
            }
        }, 1000)
}

function reserveAnswer()
{
    $fetch(config.public.apiEndpoint + config.public.apiPath + '/answer', {
        method: 'POST',
        headers: {
            Authorization: "Bearer " + token
        },
    })
}

function sendAnswer()
{
    if(yourAnswer.value != '') {
        $fetch(config.public.apiEndpoint + config.public.apiPath + '/answer', {
            method: 'PUT',
            headers: {
                Authorization: "Bearer " + token
            },
            body: {
                'answer': yourAnswer.value
            }
        })
        yourAnswer.value = ''
    } else {
        $toast.error("Per favore, scrivi qualcosa...");
    }
}

function evaluateAnswer(answerID, evaluation)
{
    $fetch(config.public.apiEndpoint + config.public.apiPath + '/answer/evaluate', {
        method: 'PUT',
        headers: {
            Authorization: "Bearer " + token
        },
        body: {
            'answer': answerID,
            'correct': evaluation
        }
    })
}

window.Echo.join('quiz')
.here((users) => {
    console.log('here', users)
    adminsList.value = [];
    usersList.value = [];
    users.forEach((u) => {
        if(u.admin) {
            adminsList.value.push(u)
        } else {
            usersList.value.push(u)
        }
    })
    checkGame()
})
.joining((user) => {
    console.log('joining', user)
    adminsList.value = adminsList.value.filter((u) => u.id !== user.id)
    usersList.value = usersList.value.filter((u) => u.id !== user.id)
    if(user.admin) { 
        adminsList.value.push(user)
    } else {
        usersList.value.push(user)
    }
})
.leaving((user) => {
    console.log('leaving', user)
    adminsList.value = adminsList.value.filter((u) => u.id !== user.id)
    usersList.value = usersList.value.filter((u) => u.id !== user.id)
})
.listen('GameStatus', (e) => {
    console.log(e)
    importFetchedData(e)
    updateGameStatus()
})
.error((error) => {
    console.error(error)
});

window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('connected')
    isConnected.value = true
});
window.Echo.connector.pusher.connection.bind('connecting', () => {
    console.log('connecting')
    isConnected.value = false
});
window.Echo.connector.pusher.connection.bind('disconnected', () => {
    console.log('disconnected')
    window.Echo.connector.pusher.connect();
});
window.Echo.connector.pusher.connection.bind('unavailable', () => {
    console.log('unavailable')
    window.Echo.connector.pusher.connect();
});

</script>

<template>
    <div class="flex h-[calc(100vh-60px)] overflow-hidden mt-[60px] items-stretch">

        <ConnectionSpinner 
        :show="!isConnected" 
        />

        <PlayersList 
        :admins="adminsList" 
        :users="usersList"
        :userId="user.id" 
        :blocked="gameData ? gameData.blocked : []" 
        :gamesPerMatch="gamesPerMatch"
        />

        <div class="flex-1 flex flex-col border-0 pl-[65px] md:pl-0">
            <div  class="flex-1 flex flex-col" v-if="uiInitialized">
                <div class="p-8">

                    <div v-if="uiStatus == 'no_game'">
                        <div v-if="user.admin">
                            <header class="bg-primary-100 p-16 text-gray-700 rounded-lg">
                                <h1 class="text-2xl font-semibold mb-4">Fai una domanda ai giocatori</h1>
                                <div class="flex items-stretch">
                                    <input type="text" placeholder="ad es. Qual'è la risposta alla domanda fondamentale?"
                                        v-model="questionText"
                                        v-on:keyup.enter="askQuestion()"
                                        class="block w-full p-4 text-gray-900 border border-gray-300 rounded-lg placeholder-gray-300 focus-visible:outline-primary-500 bg-gray-50 sm:text-md focus:ring-primary-500 focus-visible:ring-primary-500 focus-visible:border-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-200 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                    <button class="bg-primary-500 text-white text-xl font-light px-8 py-2 rounded-lg ml-2 transition-all duration-300 hover:bg-primary-300"
                                        @click="askQuestion()">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="1em" class="fill-white" viewBox="0 0 512 512"><path d="M498.1 5.6c10.1 7 15.4 19.1 13.5 31.2l-64 416c-1.5 9.7-7.4 18.2-16 23s-18.9 5.4-28 1.6L284 427.7l-68.5 74.1c-8.9 9.7-22.9 12.9-35.2 8.1S160 493.2 160 480V396.4c0-4 1.5-7.8 4.2-10.7L331.8 202.8c5.8-6.3 5.6-16-.4-22s-15.7-6.4-22-.7L106 360.8 17.7 316.6C7.1 311.3 .3 300.7 0 288.9s5.9-22.8 16.1-28.7l448-256c10.7-6.1 23.9-5.5 34 1.4z"/></svg>
                                    </button>
                                </div>
                            </header>
                        </div>
                        <div v-else>
                            <header class="bg-primary-100 p-16 text-gray-700 rounded-lg">
                                <div class="flex items-center gap-8">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="fill-primary-500 animate-spin" height="1.5em" viewBox="0 0 384 512"><path d="M24 0C10.7 0 0 10.7 0 24S10.7 48 24 48h8V67c0 40.3 16 79 44.5 107.5L158.1 256 76.5 337.5C48 366 32 404.7 32 445v19H24c-13.3 0-24 10.7-24 24s10.7 24 24 24H360c13.3 0 24-10.7 24-24s-10.7-24-24-24h-8V445c0-40.3-16-79-44.5-107.5L225.9 256l81.5-81.5C336 146 352 107.3 352 67V48h8c13.3 0 24-10.7 24-24s-10.7-24-24-24H24zM192 289.9l81.5 81.5C293 391 304 417.4 304 445v19H80V445c0-27.6 11-54 30.5-73.5L192 289.9zm0-67.9l-81.5-81.5C91 121 80 94.6 80 67V48H304V67c0 27.6-11 54-30.5 73.5L192 222.1z"/></svg>
                                    <h1 class="text-2xl font-semibold ">in attesa della domanda...</h1>
                                </div>
                            </header>
                        </div>
                    </div>

                    <div v-if="['game_over','game_winned','match_winned'].includes(uiStatus)" class="text-center">
                        <header class="bg-primary-100 p-16 text-gray-700 rounded-lg">

                            <h1 class="text-4xl text-primary-500 font-semibold" v-if="uiStatus == 'game_over'">Fine del round</h1>
                            <h1 class="text-4xl font-semibold"  v-if="uiStatus == 'game_winned'"><span class="font-black text-primary-500">{{ gameData.winner.name }}</span> vince questo round!</h1>
                            <h1 class="text-4xl font-semibold flex items-center gap-8 justify-center"  v-if="uiStatus == 'match_winned'">
                                <svg xmlns="http://www.w3.org/2000/svg" height="1.5em" class="fill-yellow-500" viewBox="0 0 576 512"><path d="M400 0H176c-26.5 0-48.1 21.8-47.1 48.2c.2 5.3 .4 10.6 .7 15.8H24C10.7 64 0 74.7 0 88c0 92.6 33.5 157 78.5 200.7c44.3 43.1 98.3 64.8 138.1 75.8c23.4 6.5 39.4 26 39.4 45.6c0 20.9-17 37.9-37.9 37.9H192c-17.7 0-32 14.3-32 32s14.3 32 32 32H384c17.7 0 32-14.3 32-32s-14.3-32-32-32H357.9C337 448 320 431 320 410.1c0-19.6 15.9-39.2 39.4-45.6c39.9-11 93.9-32.7 138.2-75.8C542.5 245 576 180.6 576 88c0-13.3-10.7-24-24-24H446.4c.3-5.2 .5-10.4 .7-15.8C448.1 21.8 426.5 0 400 0zM48.9 112h84.4c9.1 90.1 29.2 150.3 51.9 190.6c-24.9-11-50.8-26.5-73.2-48.3c-32-31.1-58-76-63-142.3zM464.1 254.3c-22.4 21.8-48.3 37.3-73.2 48.3c22.7-40.3 42.8-100.5 51.9-190.6h84.4c-5.1 66.3-31.1 111.2-63 142.3z"/></svg>
                                <div><span class="font-black text-primary-500">{{ gameData.winner.name }}</span> vince la partita!</div>
                            </h1>
                            <div v-if="user.admin" class="mt-8">
                                <button class="bg-primary-500 text-white text-xl font-light px-8 py-2 rounded-lg ml-2 transition-all duration-300 hover:bg-primary-300" @click="restartGame"
                                v-html="uiStatus == 'match_winned' ? 'Nuova partita' : 'Continua'">    
                                </button>
                            </div>
                        </header>

                    </div>  

                    <div v-if="!['game_over','no_game','game_winned','match_winned'].includes(uiStatus)">
                        <header class="bg-primary-100 p-8 text-gray-700 rounded-lg">
                            <div class="flex items-center mb-4">
                                <div class="text-2xl font-black" v-html="gameData.author.name"></div>
                                <div class="text-2xl">&nbsp;ha chiesto:</div>
                            </div>
                            <blockquote class="text-5xl italic font-light text-primary-500 dark:text-white pt-8">
                                <p>&quot;<span v-html="gameData.question"></span>&quot;</p>
                            </blockquote>
                            <div class="flex items-center text-xs text-primary-400 pt-2 justify-end">
                            </div>
                        </header>
                    </div>

                </div>

                <div class="relative flex-1 p-8">
                    <div v-for="answer in gameData.answers" v-bind:key="answer.id">

                        <div class="flex w-full mb-8 px-8" v-if="!youHaveToAnswer || user.id != answer.author.id">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center mr-4 relative">
                                <img v-bind:src="'https://i.pravatar.cc/300?u='+answer.author.id" alt="User Avatar" class="w-16 h-16 rounded-full">
                                <div class="w-full h-full flex justify-center items-center absolute top-0 left-0 opacity-80" v-if="answer.correct == false">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="4em" class=" fill-red-500" viewBox="0 0 384 512"><path d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/></svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div 
                                    class="rounded-lg flex-1 p-3 gap-3 px-8" 
                                    v-if="answer.text !== null"  
                                    v-bind:class="answer.correct == null ? 'animate-pulse bg-primary-100' : (answer.correct == false ? 'bg-red-100 border border-red-500 text-red-500' : 'bg-green-200 border border-green-500 text-green-500')"
                                >
                                    <div class="text-xs font-bold">{{ answer.author.name }} ha risposto:</div>
                                    <div class="text-xl">{{ answer.text }}</div>
                                </div>
                                <div class="rounded-lg flex-1 p-3 gap-3 px-8" v-else>
                                    <div class="text-gray-400 text-base my-2">
                                        {{ answer.author.name }} ha prenotato la risposta
                                    </div>
                                </div>
    
                                <div v-if="answer.correct == null && answer.text !== null" class="text-gray-600 text-sm pt-2">
                                    <div v-if="!user.admin" class="flex items-center gap-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="fill-primary-500 animate-spin" height="1em" viewBox="0 0 384 512"><path d="M24 0C10.7 0 0 10.7 0 24S10.7 48 24 48h8V67c0 40.3 16 79 44.5 107.5L158.1 256 76.5 337.5C48 366 32 404.7 32 445v19H24c-13.3 0-24 10.7-24 24s10.7 24 24 24H360c13.3 0 24-10.7 24-24s-10.7-24-24-24h-8V445c0-40.3-16-79-44.5-107.5L225.9 256l81.5-81.5C336 146 352 107.3 352 67V48h8c13.3 0 24-10.7 24-24s-10.7-24-24-24H24zM192 289.9l81.5 81.5C293 391 304 417.4 304 445v19H80V445c0-27.6 11-54 30.5-73.5L192 289.9zm0-67.9l-81.5-81.5C91 121 80 94.6 80 67V48H304V67c0 27.6-11 54-30.5 73.5L192 222.1z"/></svg>
                                        valutazione in corso...
                                    </div>
                                    <div v-else class="bg-green-100 rounded-lg p-4 flex justify-between items-center">
                                        <div class="text-green-800 font-bold">E' la risposta corretta?</div>
                                        <div class="flex gap-8">
                                            <button 
                                            type="button" 
                                            class="focus:outline-none text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800 w-12 text-center py-2" 
                                            @click="evaluateAnswer(answer.id, true)"
                                            >Si</button>
    
                                            <button 
                                            type="button" 
                                            class="focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-smdark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900 w-12 text-center py-2" 
                                            @click="evaluateAnswer(answer.id, false)"
                                            >No</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="mb-16">
                            <div class="bg-primary-100 p-8 text-gray-700 rounded-lg flex">
                                <div class="w-16 h-16 rounded-full flex items-center justify-center mr-4 relative">
                                    <img v-bind:src="'https://i.pravatar.cc/300?u='+answer.author.id" alt="User Avatar" class="w-16 h-16 rounded-full">
                                </div>
                                <div class="flex items-stretch flex-1">
                                    <input type="text" placeholder="Scrivi la tua risposta..."
                                        v-model="yourAnswer"
                                        v-on:keyup.enter="sendAnswer()"
                                        class="block w-full p-4 text-gray-900 border border-gray-300 rounded-lg placeholder-gray-300 focus-visible:outline-primary-500 bg-gray-50 sm:text-md focus:ring-primary-500 focus-visible:ring-primary-500 focus-visible:border-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-200 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                    <button class="bg-primary-500 text-white text-xl font-light px-8 py-2 rounded-lg ml-2 transition-all duration-300 hover:bg-primary-300"
                                        @click="sendAnswer">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="1em" class="fill-white" viewBox="0 0 512 512"><path d="M498.1 5.6c10.1 7 15.4 19.1 13.5 31.2l-64 416c-1.5 9.7-7.4 18.2-16 23s-18.9 5.4-28 1.6L284 427.7l-68.5 74.1c-8.9 9.7-22.9 12.9-35.2 8.1S160 493.2 160 480V396.4c0-4 1.5-7.8 4.2-10.7L331.8 202.8c5.8-6.3 5.6-16-.4-22s-15.7-6.4-22-.7L106 360.8 17.7 316.6C7.1 311.3 .3 300.7 0 288.9s5.9-22.8 16.1-28.7l448-256c10.7-6.1 23.9-5.5 34 1.4z"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
    
    
                    <div class="absolute top-0 left-0 w-full h-full bg-[#ffffff80] flex justify-center items-center" v-if="uiStatus == 'game_started'">
                        <div class="flex flex-col items-center justify-center">
                            <div class="relative w-[10rem] h-[10rem]">
                                <svg xmlns="http://www.w3.org/2000/svg" height="10rem" class="animate-spin fill-primary-300" viewBox="0 0 512 512"><path d="M222.7 32.1c5 16.9-4.6 34.8-21.5 39.8C121.8 95.6 64 169.1 64 256c0 106 86 192 192 192s192-86 192-192c0-86.9-57.8-160.4-137.1-184.1c-16.9-5-26.6-22.9-21.5-39.8s22.9-26.6 39.8-21.5C434.9 42.1 512 140 512 256c0 141.4-114.6 256-256 256S0 397.4 0 256C0 140 77.1 42.1 182.9 10.6c16.9-5 34.8 4.6 39.8 21.5z"/></svg>
                                <div class="text-[5rem] font-light absolute top-0 left-0 w-full h-full flex justify-center items-center" 
                                v-html="waitingSeconds" v-bind:class="waitingSeconds < 10 ? 'text-red-500' : ''"
                                v-if="waitingSeconds >= 0"
                                ></div>
                            </div>
                            <div class="flex justify-center mt-16" v-if="youCanReserve">
                                <button class="rounded-full w-48 bg-primary-500 hover:bg-green-500 transition-all duration-300 hover:shadow-2xl text-white flex items-center justify-center text-5xl font-bold aspect-square shadow-lg"
                                @click="reserveAnswer()">
                                    LA SO!  
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                

                

            </div>
            <div v-else class="h-full w-full flex items-center justify-center">
                <div class="flex justify-center flex-col items-center">
                    <Spinner />
                    <div>caricamento partita</div>
                </div>
            </div>
        </div>

    </div>
</template>