document.addEventListener('DOMContentLoaded', () => {
    const addAchievementBtn = document.getElementById('addAchievementBtn');
    const achievementForm = document.getElementById('achievementForm');
    const saveAchievementBtn = document.getElementById('saveAchievementBtn');
    const achievementsList = document.getElementById('achievementsList');
    const exportAchievementsBtn = document.getElementById('exportAchievementsBtn');
    const importFile = document.getElementById('importFile');
    const fileName = document.getElementById('fileName');

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

    const displayAchievements = () => {
        achievementsList.innerHTML = '';
        achievements.forEach(achievement => {
            const achievementDiv = document.createElement('div');
            achievementDiv.className = 'achievement';
            achievementDiv.classList.add(achievement.state);
            achievementDiv.innerHTML = `<h2>${achievement.title}</h2><p>${achievement.description}</p>`;
            achievementDiv.addEventListener('click', () => toggleAchievementState(achievement.id, achievement.state));
            achievementsList.appendChild(achievementDiv);
        });
    };

    displayAchievements();

    exportAchievementsBtn.addEventListener('click', () => {
        window.location.href = 'index.php?export=true';
    });

    importFile.addEventListener('change', () => {
        const file = importFile.files[0];
        if (file) {
            fileName.textContent = `Selected file: ${file.name}`;
        } else {
            fileName.textContent = '';
        }
    });
});
