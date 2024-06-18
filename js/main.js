document.addEventListener('DOMContentLoaded', () => {
    const addAchievementBtn = document.getElementById('addAchievementBtn');
    const achievementForm = document.getElementById('achievementForm');
    const saveAchievementBtn = document.getElementById('saveAchievementBtn');
    const achievementsList = document.getElementById('achievementsList');
    const exportAchievementsBtn = document.getElementById('exportAchievementsBtn');
    const importFile = document.getElementById('importFile');
    const fileName = document.getElementById('fileName');
    const importAchievementsBtn = document.getElementById('importAchievementsBtn');
    const counter = document.getElementById('counter');
    const toggleModeBtn = document.getElementById('toggleModeBtn');
    const sortTitleAZBtn = document.getElementById('sortTitleAZBtn');
    const sortTitleZABtn = document.getElementById('sortTitleZABtn');
    const sortDateOldestBtn = document.getElementById('sortDateOldestBtn');
    const sortDateNewestBtn = document.getElementById('sortDateNewestBtn');

    let isDay = true;

    toggleModeBtn.addEventListener('click', () => {
        document.body.classList.toggle('day', isDay);
        document.body.classList.toggle('night', !isDay);
        isDay = !isDay;
    });

    addAchievementBtn.addEventListener('click', () => {
        achievementForm.style.display = 'block';
    });

    saveAchievementBtn.addEventListener('click', () => {
        const title = document.getElementById('title').value;
        const description = document.getElementById('description').value;

        fetch('index.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `title=${encodeURIComponent(title)}&description=${encodeURIComponent(description)}`
        })
        .then(response => response.text())
        .then(data => {
            console.log(data);
            location.reload();
        });
    });

    const deleteAchievement = (id) => {
        if (confirm('Are you sure you want to delete this achievement?')) {
            fetch('inc/functions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `delete=true&id=${id}`
            })
            .then(response => response.text())
            .then(data => {
                console.log(data);
                location.reload();
            });
        }
    };

    const editAchievement = (id, currentTitle, currentDescription) => {
        // Create the edit form dynamically
        const editForm = document.createElement('div');
        editForm.innerHTML = `
            <div class="edit-form">
                <h2>Edit Achievement</h2>
                <label for="editTitle">Title:</label>
                <input type="text" id="editTitle" value="${currentTitle}">
                <label for="editDescription">Description:</label>
                <textarea id="editDescription">${currentDescription}</textarea>
                <button id="saveEditBtn">Save</button>
                <button id="cancelEditBtn">Cancel</button>
            </div>
        `;
        document.body.appendChild(editForm);

        // Add event listeners for save and cancel buttons
        document.getElementById('saveEditBtn').addEventListener('click', () => {
            const newTitle = document.getElementById('editTitle').value;
            const newDescription = document.getElementById('editDescription').value;

            fetch('inc/functions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `edit=true&id=${id}&title=${encodeURIComponent(newTitle)}&description=${encodeURIComponent(newDescription)}`
            })
            .then(response => response.text())
            .then(data => {
                console.log(data);
                location.reload();
            });
        });

        document.getElementById('cancelEditBtn').addEventListener('click', () => {
            document.body.removeChild(editForm);
        });
    };

    const toggleAchievementState = (id, currentState) => {
        let newState;
        switch (currentState) {
            case 'default':
                newState = 'done';
                break;
            case 'done':
                newState = 'not_done';
                break;
            case 'not_done':
                newState = 'default';
                break;
        }

        fetch('inc/functions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}&state=${newState}`
        })
        .then(response => response.text())
        .then(data => {
            console.log(data);
            location.reload();
        });
    };

    const updateCounter = () => {
        const total = achievements.length;
        const doneCount = achievements.filter(a => a.state === 'done').length;
        counter.textContent = `${doneCount}/${total} achievements done`;
        exportAchievementsBtn.disabled = total === 0;
    };

    const displayAchievements = (sortedAchievements) => {
        achievementsList.innerHTML = '';
        sortedAchievements.forEach(achievement => {
            const achievementDiv = document.createElement('div');
            achievementDiv.className = 'achievement';
            achievementDiv.classList.add(achievement.state);
            achievementDiv.dataset.id = achievement.id;
            achievementDiv.innerHTML = `
                <h2>${achievement.title}</h2>
                <p>${achievement.description}</p>
                <i class="fas fa-edit edit-icon"></i>
                <i class="fas fa-trash delete-icon"></i>
            `;

            achievementDiv.addEventListener('click', () => toggleAchievementState(achievement.id, achievement.state));

            const editIcon = achievementDiv.querySelector('.edit-icon');
            editIcon.addEventListener('click', (e) => {
                e.stopPropagation();  // Prevent the click event from propagating to the achievement card
                editAchievement(achievement.id, achievement.title, achievement.description);
            });

            const deleteIcon = achievementDiv.querySelector('.delete-icon');
            deleteIcon.addEventListener('click', (e) => {
                e.stopPropagation();  // Prevent the click event from propagating to the achievement card
                deleteAchievement(achievement.id);
            });

            achievementsList.appendChild(achievementDiv);
        });
        updateCounter();
    };

    const sortable = new Sortable(achievementsList, {
        animation: 150,
        onEnd: function (evt) {
            const order = Array.from(achievementsList.children).map(child => child.dataset.id);

            fetch('inc/functions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `order=${JSON.stringify(order)}`
            })
            .then(response => response.text())
            .then(data => {
                console.log('Order updated:', data);
            });
        }
    });

    displayAchievements(achievements);

    exportAchievementsBtn.addEventListener('click', (e) => {
        e.preventDefault();  // Prevent the default action to allow the confirmation
        if (confirm("Are you sure you want to export the achievements?")) {
            window.location.href = 'index.php?export=true';
        }
    });

    importFile.addEventListener('change', () => {
        const file = importFile.files[0];
        if (file) {
            fileName.textContent = `Selected file: ${file.name}`;
            importAchievementsBtn.disabled = false;
        } else {
            fileName.textContent = '';
            importAchievementsBtn.disabled = true;
        }
    });

    sortTitleAZBtn.addEventListener('click', () => {
        const sortedAchievements = [...achievements].sort((a, b) => a.title.localeCompare(b.title));
        displayAchievements(sortedAchievements);
    });

    sortTitleZABtn.addEventListener('click', () => {
        const sortedAchievements = [...achievements].sort((a, b) => b.title.localeCompare(a.title));
        displayAchievements(sortedAchievements);
    });

    sortDateOldestBtn.addEventListener('click', () => {
        const sortedAchievements = [...achievements].sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
        displayAchievements(sortedAchievements);
    });

    sortDateNewestBtn.addEventListener('click', () => {
        const sortedAchievements = [...achievements].sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        displayAchievements(sortedAchievements);
    });
});

function confirmDelete() {
    return confirm("Are you sure you want to delete all achievements? This action cannot be undone.");
}
