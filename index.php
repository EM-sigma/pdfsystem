<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landing Page</title>
    <style>
        * {
            margin: 50px 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #fff;
            font-family: Andale Mono, monospace;
        }

        .container h2 {
            font-size: 3rem;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        
        .link-container {
            position: relative;
            width: 100%;
        }

        .link-container img {
            width: 100%;
            height: 130vh;
            object-fit: cover;
            margin: 0;
            filter: blur(1px); 
        }

        .link {
            width: 100%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .link h1 {
            font-family: Manrope, sans-serif;
            font-size: 70px;
            color: #FFFE58;
            width: 100%;
            text-align: center;
            margin: 0;
            -webkit-text-stroke: 1px #000;
        }

        .about {
            padding-left: 50px;
        }

        .about .info {
            font-size: 1.2rem;
            width: 50%;
            text-align: left;
            font-weight: 0;
            margin: 0;
        }

        .about p:first-child {
            margin: 0;
            margin-bottom: 10px;
            text-align: left;
            font-size: 2.5rem;
        }

        .link {
            width: 100%;
            display: flex;
            align-items: center;
            flex-direction: column;
        }

        .link-a a {
            padding: 10px 20px;
            font-size: 1.5rem;
            border-radius: 25px;
            text-decoration: none;
            color: #000;
            background: #FFFE58;
            display: inline-block;
            margin: 0 auto;

            border: 4px double transparent;
        }

        .link-a a:first-child {
            margin-right: 10px;
        }

        .link-a a:hover {
            border-color: #333;
        }

        @media only screen and (max-width: 700px) {
            .link h1 {
                font-size: 2rem;
            }
            .link-a a {
                font-size: 1rem;
            }
            .about .info {
                font-size: 13px;
                width: 90%;
            }
            .about {
                padding-left: 20px;
            }
            .link-container img {
                height: 70vh;
            }
            .container h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Knowledge Management Office</h2>
        <div class="link-container">
            <img src="img/essu-bg.jpg" alt="">
            <div class="link">
                <h1>Student Research Title and Workstation Files</h1>
                <div class="link-a">
                    <a href="sign-up.php">Sign Up</a>
                    <a href="login.php">Login</a>
                </div>     
            </div>  
        </div>
        <div class="about">
            <p>About</p>
            <p class="info">
                The workstation files and student research titles at Borongan ESSU (Eastern Samar State University) serve as 
                essential resources for both academic and administrative purposes. These files contain a wealth of information on 
                various research projects conducted by students across different fields of study. The collection 
                showcases the innovative ideas and hard work of students, offering a glimpse into their scholarly contributions. 
                Additionally, these research titles reflect the diverse academic interests within the institution, 
                ranging from local community studies to scientific advancements. The availability and accessibility of 
                such materials not only support the ongoing learning process but also encourage future students to engage 
                in critical thinking, exploration, and knowledge creation within their respective
            </p>
        </div>
    </div>
</body>
</html>