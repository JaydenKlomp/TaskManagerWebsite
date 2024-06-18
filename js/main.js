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

    const displayAchievements = () => {
        achievementsList.innerHTML = '';
        achievements.forEach(achievement => {
            const achievementDiv = document.createElement('div');
            achievementDiv.className = 'achievement';
            achievementDiv.classList.add(achievement.state);
            achievementDiv.dataset.id = achievement.id;
            achievementDiv.innerHTML = `<h2>${achievement.title}</h2><p>${achievement.description}</p>`;
            achievementDiv.addEventListener('click', () => toggleAchievementState(achievement.id, achievement.state));
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

    displayAchievements();

    exportAchievementsBtn.addEventListener('click', () => {
        window.location.href = 'index.php?export=true';
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
});

function confirmDelete() {
    return confirm("Are you sure you want to delete all achievements? This action cannot be undone.");
}
