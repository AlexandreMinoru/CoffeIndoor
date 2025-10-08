  const firebaseConfig = {
      apiKey: "AIzaSyC4KxhSrvjdyyW24ZYHvOt9E2Cw8C_OsQg",
      authDomain: "coffeindoor-a0ded.firebaseapp.com",
      databaseURL: "https://coffeindoor-a0ded-default-rtdb.firebaseio.com",
      projectId: "coffeindoor-a0ded",
      storageBucket: "coffeindoor-a0ded.appspot.com",
      messagingSenderId: "24965367285",
      appId: "1:24965367285:web:b73d7bd7a6ad1ad9b88cbc",
      measurementId: "G-YWCSN661EV"
    };

    firebase.initializeApp(firebaseConfig);
    const database = firebase.database();
    const storage = firebase.storage();

    const form = document.getElementById('cafeteria-form');
    const fileInput = document.getElementById('fotos');
    const formMessage = document.getElementById('form-message');


    // Upload para Firebase Storage
    function uploadFile(file) {
      return new Promise((resolve, reject) => {
        const timestamp = Date.now();
        const storageRef = storage.ref(`cafeterias/${timestamp}_${file.name}`);
        const uploadTask = storageRef.put(file);

        uploadTask.on('state_changed',
          null,
          (error) => reject(error),
          () => {
            uploadTask.snapshot.ref.getDownloadURL().then(resolve);
          }
        );
      });
    }

    form.addEventListener('submit', async function (event) {
      event.preventDefault();
      formMessage.style.color = 'black';
      formMessage.textContent = "Enviando... Por favor, aguarde.";

      const nome = form.name.value.trim();
      const instagram = form.email.value.trim();
      const mensagem = form.message.value.trim();

      if (!nome || !instagram || !mensagem) {
        formMessage.style.color = 'red';
        formMessage.textContent = "Por favor, preencha todos os campos obrigatórios.";
        return;
      }

      const files = fileInput.files;
      let urls = [];

      try {
        // Upload das imagens
        for (let i = 0; i < files.length; i++) {
          const url = await uploadFile(files[i]);
          urls.push(url);
        }

        // Salvar no Realtime Database
        const newRef = database.ref('cafeterias').push();
        await newRef.set({
          nome: nome,
          instagram: instagram,
          mensagem: mensagem,
          fotos: urls,
          timestamp: Date.now()
        });

        formMessage.style.color = 'green';
        formMessage.textContent = "☕ Cafeteria enviada com sucesso!";
        form.reset();
        preview.innerHTML = '';
      } catch (error) {
        console.error("Erro ao enviar:", error);
        formMessage.style.color = 'red';
        formMessage.textContent = "Erro ao enviar. Verifique sua conexão ou tente novamente.";
      }
    });
  