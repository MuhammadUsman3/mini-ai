<style>
    /* CSS for Typing Indicator */
    #typing-indicator {
        display: none;
        padding: 10px;
    }

    .typing-text {
        font-style: italic;
        color: #aaa;
    }
</style>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Open AI') }}
        </h2>
    </x-slot>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- HTML structure for search input and displaying search results -->
                    <div class="flex items-center space-x-2">
                        <input type="text" id="searchInput" class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500" placeholder="Search...">
                        <button onclick="searchMessages()" class="bg-blue-500 hover:bg-blue-700 text-black font-bold py-2 px-4 border border-blue-700 rounded">Search</button>
                    </div>

                    <!-- Container for displaying search results -->
                    <div id="searchResults" class="border border-gray-300 rounded-md mt-4 p-4 hidden">
                        <!-- Search results will be displayed here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    {{_('Welcome !!!')}}
                    <span class="justify-end flex">
                        <select id="personality" class="justify-end">
                            <option value="formal">Formal</option>
                            <option value="friendly">Friendly</option>
                            <option value="humorous">Humorous</option>
                        </select>
                    </span>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md w-80">
                <!-- Messages -->
                <div class="p-4 {{$messages->count() > 0 ? ' ' : ' hidden'}}" id="chatbox">
                    @foreach ($messages as $message)
                    <div class="flex justify-end mb-2 bg-blue-100 text-blue-800 rounded-lg p-2'">
                        {{ $message->user_message }}
                    </div>
                    <div class='flex justify-left mb-2 bg-blue-100 text-blue-800 rounded-lg p-2'>
                        {{ $message->bot_response }}
                    </div>
                    @endforeach
                </div>


                <!-- Typing indicator -->
                <div id="typing-indicator" style="display: none;">
                    <p class="typing-text">Typing...</p>
                </div>

                <!-- New message input -->
                <div class="flex border-t p-2">
                    <input type="text" id="messageInput" class="flex-1 border rounded-l-lg p-2" placeholder="Type your message...">
                    <button onclick="sendMessage()" class="bg-blue-500 hover:bg-blue-700 text-black font-bold py-2 px-4 border border-blue-700 rounded">Send</button>
                    <button onclick="stopSearch()" id="stopButton" class="bg-blue-500 hover:bg-blue-700 text-black font-bold py-2 px-4 border border-blue-700 rounded">Stop</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<!-- Import Axios -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
    let cancelToken;
    // Send message function with typing indicator
    function sendMessage() {
        let message = document.getElementById('messageInput');
        let personality = document.getElementById('personality').value;
        const stopButton = document.getElementById('stopButton');
        stopButton.classList.remove('hidden');

        // Show typing indicator
        showTypingIndicator();

        // Cancel the previous request if it exists
        if (cancelToken) {
            cancelToken.cancel('Operation canceled by the user.');
        }

        cancelToken = axios.CancelToken.source();

        axios.post('/send-message', {
                message: message.value.trim(),
                personality: personality
            }, {
                cancelToken: cancelToken.token
            })
            .then(function(response) {
                if (response.data.success == true) {
                    message.value = '';
                    hideTypingIndicator(); // Hide typing indicator
                    displayMessage(response.data.history[0]);
                }
            })
            .catch(function(error) {
                console.error('Error sending message:', error);
                hideTypingIndicator(); // Hide typing indicator on error
            }).finally(function() {
                stopButton.classList.add('hidden');
            });
    }

    // Function to stop the ongoing search request
    function stopSearch() {
        if (cancelToken) {
            cancelToken.cancel('Operation canceled by the user.');
        }
    }

    function showTypingIndicator() {
        const typingIndicator = document.getElementById('typing-indicator');
        typingIndicator.style.display = 'block';
    }

    function hideTypingIndicator() {
        const typingIndicator = document.getElementById('typing-indicator');
        typingIndicator.style.display = 'none';
    }

    function displayMessage(history) {
        Object.keys(history).forEach(key => {
            const chatBox = document.getElementById('chatbox');
            message = history[key];
            const messageDiv = document.createElement('div');
            if (key == "user") {
                messageDiv.classList.add('flex', 'justify-end', 'mb-2', 'bg-blue-100', 'text-blue-800', 'rounded-lg', 'p-2');
            } else {
                messageDiv.classList.add('flex', 'justify-left', 'mb-2', 'bg-blue-100', 'text-blue-800', 'rounded-lg', 'p-2');
            }
            chatBox.classList.remove('hidden');
            messageDiv.innerText = message;
            chatBox.appendChild(messageDiv);
            chatBox.scrollTop = chatBox.scrollHeight;
        });
    }

    // Function to search messages
    function searchMessages() {
        let searchTerm = document.getElementById('searchInput').value.trim();
        const searchResultsContainer = document.getElementById('searchResults');

        axios.get('/search-messages', {
                params: {
                    searchTerm: searchTerm
                }
            })
            .then(function(response) {
                if (response.data.success) {
                    searchResultsContainer.classList.remove('hidden');
                    displaySearchResults(response.data.searchResults, searchResultsContainer);
                } else {
                    searchResultsContainer.classList.add('hidden');
                    displayNoResults(searchResultsContainer);
                }
            })
            .catch(function(error) {
                console.error('Error searching messages:', error);
                searchResultsContainer.classList.add('hidden');
                displayError(searchResultsContainer);
            });
    }

    // Function to display search results
    function displaySearchResults(results, container) {
        container.innerHTML = ''; // Clear previous results

        if (results.length > 0) {
            results.forEach(result => {
                const messageDiv = document.createElement('div');
                messageDiv.classList.add('border-b', 'py-2');
                messageDiv.innerText = `User: ${result.user_message} - Bot: ${result.bot_response}`;
                container.appendChild(messageDiv);
            });
        } else {
            displayNoResults(container);
        }
    }

    // Function to display no results message
    function displayNoResults(container) {
        container.innerHTML = '<p class="text-gray-500">No results found.</p>';
    }

    // Function to display error message
    function displayError(container) {
        container.innerHTML = '<p class="text-red-500">Error fetching search results. Please try again later.</p>';
    }
</script>